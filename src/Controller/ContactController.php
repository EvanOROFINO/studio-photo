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

        // Pré-remplissage si l'utilisateur vient d'un forfait vidéo (?forfait=signature)
        $selectedPackage = null;
        $forfait = $request->query->get('forfait');
        if ($forfait && $request->isMethod('GET')) {
            $selectedPackage = ucfirst($forfait);
            $contactRequest->setProjectType('pro');
            $contactRequest->setMessage(sprintf(
                "Bonjour,\n\nJe suis intéressé(e) par le forfait « %s ». Pourriez-vous me recontacter pour en discuter ?\n\nMon projet : ",
                $selectedPackage
            ));
        }

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
            'selectedPackage' => $selectedPackage,
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
