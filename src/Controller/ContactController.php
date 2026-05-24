<?php

namespace App\Controller;

use App\Entity\ContactRequest;
use App\Form\ContactRequestType;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        MailService $mailService,
    ): Response {
        $contactRequest = new ContactRequest();
        $form = $this->createForm(ContactRequestType::class, $contactRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->isSpam($request)) {
                $this->addFlash('success', 'Merci ! Votre message a bien été envoyé. Je vous réponds sous 48h.');
                return $this->redirectToRoute('app_contact');
            }

            $em->persist($contactRequest);
            $em->flush();

            $mailService->sendContactReceivedToAdmin($contactRequest);
            $mailService->sendContactReceivedToClient($contactRequest);

            $this->addFlash('success', 'Merci ! Votre message a bien été envoyé. Je vous réponds sous 48h.');

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form,
        ]);
    }

    private function isSpam(Request $request): bool
    {
        if (!empty($request->request->get('website'))) {
            return true;
        }
        $renderedAt = (int) $request->request->get('rendered_at', '0');
        if ($renderedAt > 0 && (time() - $renderedAt) < 3) {
            return true;
        }
        return false;
    }
}
