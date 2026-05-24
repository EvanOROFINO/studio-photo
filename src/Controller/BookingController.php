<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Service;
use App\Form\BookingType;
use App\Repository\BookingRepository;
use App\Service\AvailabilityService;
use App\Service\MailService;
use App\Service\StripeCheckoutService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BookingController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly StripeCheckoutService $stripe,
        private readonly AvailabilityService $availability,
        private readonly MailService $mailService,
    ) {
    }

    #[Route('/reservation/service/{id}', name: 'app_booking_new', requirements: ['id' => '\d+'])]
    public function new(Service $service, Request $request): Response
    {
        if (!$service->isActive() || !$service->getPriceFrom()) {
            $this->addFlash('danger', 'Cette prestation n\'est pas réservable en ligne. Merci de me contacter.');
            return $this->redirectToRoute('app_contact');
        }

        $total = (float) $service->getPriceFrom();
        $deposit = round($total * 0.30, 2);

        $booking = new Booking();
        $booking->setService($service);
        $booking->setAmountTotal((string) $total);
        $booking->setDepositAmount((string) $deposit);

        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->availability->isAvailable($booking->getEventDate())) {
                $form->get('eventDate')->addError(new \Symfony\Component\Form\FormError(
                    'Désolé, cette date n\'est plus disponible. Choisissez-en une autre.'
                ));
            } else {
                $this->em->persist($booking);
                $this->em->flush();

                return $this->redirectToRoute('app_booking_checkout', ['reference' => $booking->getReference()]);
            }
        }

        return $this->render('booking/new.html.twig', [
            'service' => $service,
            'booking' => $booking,
            'form' => $form,
            'total' => $total,
            'deposit' => $deposit,
            'demo_mode' => !$this->stripe->isLiveMode(),
        ]);
    }

    #[Route('/reservation/{reference}/checkout', name: 'app_booking_checkout', requirements: ['reference' => 'B-[A-F0-9]+'])]
    public function checkout(string $reference, BookingRepository $repository): Response
    {
        $booking = $repository->findOneByReference($reference);
        if (!$booking) {
            throw $this->createNotFoundException();
        }

        if ($booking->isPaid()) {
            return $this->redirectToRoute('app_booking_success', ['reference' => $reference]);
        }

        $session = $this->stripe->createCheckoutSession($booking);

        if ($session === null) {
            // Demo mode: mark as paid immediately and redirect to success
            $booking->setStatus(Booking::STATUS_PAID);
            $booking->setPaidAt(new \DateTimeImmutable());
            $booking->setStripeSessionId('demo_'.bin2hex(random_bytes(8)));
            $this->em->flush();
            $this->mailService->sendBookingConfirmation($booking);
            return $this->redirectToRoute('app_booking_success', ['reference' => $reference]);
        }

        $booking->setStripeSessionId($session['id']);
        $this->em->flush();

        return new RedirectResponse($session['url']);
    }

    #[Route('/reservation/{reference}/succes', name: 'app_booking_success', requirements: ['reference' => 'B-[A-F0-9]+'])]
    public function success(string $reference, Request $request, BookingRepository $repository): Response
    {
        $booking = $repository->findOneByReference($reference);
        if (!$booking) {
            throw $this->createNotFoundException();
        }

        // If returning from real Stripe checkout, confirm session status
        $sessionId = $request->query->get('session_id');
        if ($sessionId && $this->stripe->isLiveMode() && !$booking->isPaid()) {
            $info = $this->stripe->retrieveSession($sessionId);
            if ($info && $info['payment_status'] === 'paid') {
                $booking->setStatus(Booking::STATUS_PAID);
                $booking->setPaidAt(new \DateTimeImmutable());
                $booking->setStripePaymentIntentId((string) $info['payment_intent']);
                $this->em->flush();
                $this->mailService->sendBookingConfirmation($booking);
            }
        }

        return $this->render('booking/success.html.twig', [
            'booking' => $booking,
            'demo_mode' => !$this->stripe->isLiveMode(),
        ]);
    }

    #[Route('/reservation/{reference}/annule', name: 'app_booking_cancel', requirements: ['reference' => 'B-[A-F0-9]+'])]
    public function cancel(string $reference, BookingRepository $repository): Response
    {
        $booking = $repository->findOneByReference($reference);
        if (!$booking) {
            throw $this->createNotFoundException();
        }

        if (!$booking->isPaid() && $booking->getStatus() !== Booking::STATUS_CANCELLED) {
            $booking->setStatus(Booking::STATUS_CANCELLED);
            $this->em->flush();
        }

        return $this->render('booking/cancel.html.twig', [
            'booking' => $booking,
        ]);
    }

    #[Route('/webhook/stripe', name: 'app_booking_webhook', methods: ['POST'])]
    public function webhook(Request $request, BookingRepository $repository, LoggerInterface $logger): Response
    {
        $event = $this->stripe->parseWebhook($request);
        if ($event === null) {
            return new JsonResponse(['error' => 'invalid signature or demo mode'], 400);
        }

        if ($event['type'] === 'checkout.session.completed') {
            $session = $event['data'];
            $reference = $session->client_reference_id ?? null;
            if ($reference) {
                $booking = $repository->findOneByReference($reference);
                if ($booking && !$booking->isPaid()) {
                    $booking->setStatus(Booking::STATUS_PAID);
                    $booking->setPaidAt(new \DateTimeImmutable());
                    $booking->setStripePaymentIntentId((string) $session->payment_intent);
                    $this->em->flush();
                    $logger->info('Booking paid via webhook', ['reference' => $reference]);
                }
            }
        }

        return new JsonResponse(['ok' => true]);
    }
}
