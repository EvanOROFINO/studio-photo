<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use App\Service\CartService;
use App\Service\StripeCheckoutService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OrderController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CartService $cart,
        private readonly StripeCheckoutService $stripe,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route('/commande', name: 'app_order_new')]
    public function new(Request $request): Response
    {
        if ($this->cart->isEmpty()) {
            $this->addFlash('info', 'Votre panier est vide.');
            return $this->redirectToRoute('app_shop_index');
        }

        $order = $this->cart->buildOrder();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order->setStatus(Order::STATUS_PENDING);
            $order->recalculateTotals();
            $this->em->persist($order);
            $this->em->flush();

            return $this->redirectToRoute('app_order_checkout', ['reference' => $order->getReference()]);
        }

        return $this->render('shop/checkout.html.twig', [
            'form' => $form,
            'order' => $order,
            'items' => $this->cart->getDetailedItems(),
            'subtotal' => $this->cart->getSubtotal(),
            'shipping' => (float) $order->getShippingFee(),
            'demo_mode' => !$this->stripe->isLiveMode(),
        ]);
    }

    #[Route('/commande/{reference}/payer', name: 'app_order_checkout', requirements: ['reference' => 'O-[A-F0-9]+'])]
    public function checkout(string $reference, OrderRepository $repository): Response
    {
        $order = $repository->findOneByReference($reference);
        if (!$order) {
            throw $this->createNotFoundException();
        }
        if ($order->isPaid()) {
            return $this->redirectToRoute('app_order_success', ['reference' => $reference]);
        }

        if (!$this->stripe->isLiveMode()) {
            // Demo mode: mark as paid + clear cart + redirect to success
            $order->setStatus(Order::STATUS_PAID);
            $order->setPaidAt(new \DateTimeImmutable());
            $order->setStripeSessionId('demo_'.bin2hex(random_bytes(8)));
            foreach ($order->getItems() as $item) {
                if ($item->getProduct()) {
                    $item->getProduct()->decreaseStock($item->getQuantity());
                }
            }
            $this->em->flush();
            $this->cart->clear();
            return $this->redirectToRoute('app_order_success', ['reference' => $reference]);
        }

        // Live mode: build line items for Stripe
        $lineItems = [];
        foreach ($order->getItems() as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => (int) round(((float) $item->getUnitPrice()) * 100),
                    'product_data' => ['name' => $item->getProductTitle()],
                ],
                'quantity' => $item->getQuantity(),
            ];
        }
        $lineItems[] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => (int) round(((float) $order->getShippingFee()) * 100),
                'product_data' => ['name' => 'Frais de port'],
            ],
            'quantity' => 1,
        ];

        try {
            $stripeClient = new \Stripe\StripeClient($this->stripe->isLiveMode() ? $_ENV['STRIPE_SECRET_KEY'] : '');
            $session = $stripeClient->checkout->sessions->create([
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'customer_email' => $order->getCustomerEmail(),
                'client_reference_id' => $order->getReference(),
                'metadata' => ['order_reference' => $order->getReference()],
                'line_items' => $lineItems,
                'success_url' => $this->urlGenerator->generate(
                    'app_order_success',
                    ['reference' => $order->getReference(), 'session_id' => '{CHECKOUT_SESSION_ID}'],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ),
                'cancel_url' => $this->urlGenerator->generate(
                    'app_order_cancel',
                    ['reference' => $order->getReference()],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ),
            ]);
        } catch (\Throwable $e) {
            $this->addFlash('danger', 'Erreur lors de la création du paiement : '.$e->getMessage());
            return $this->redirectToRoute('app_cart_show');
        }

        $order->setStripeSessionId($session->id);
        $this->em->flush();

        return new RedirectResponse($session->url);
    }

    #[Route('/commande/{reference}/succes', name: 'app_order_success', requirements: ['reference' => 'O-[A-F0-9]+'])]
    public function success(string $reference, OrderRepository $repository): Response
    {
        $order = $repository->findOneByReference($reference);
        if (!$order) {
            throw $this->createNotFoundException();
        }
        return $this->render('shop/success.html.twig', [
            'order' => $order,
            'demo_mode' => !$this->stripe->isLiveMode(),
        ]);
    }

    #[Route('/commande/{reference}/annule', name: 'app_order_cancel', requirements: ['reference' => 'O-[A-F0-9]+'])]
    public function cancel(string $reference, OrderRepository $repository): Response
    {
        $order = $repository->findOneByReference($reference);
        if (!$order) {
            throw $this->createNotFoundException();
        }
        if (!$order->isPaid() && $order->getStatus() !== Order::STATUS_CANCELLED) {
            $order->setStatus(Order::STATUS_CANCELLED);
            $this->em->flush();
        }
        return $this->render('shop/cancel.html.twig', [
            'order' => $order,
        ]);
    }
}
