<?php

namespace App\Controller;

use App\Entity\NewsletterSubscriber;
use App\Repository\NewsletterSubscriberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class NewsletterController extends AbstractController
{
    #[Route('/newsletter/subscribe', name: 'app_newsletter_subscribe', methods: ['POST'])]
    public function subscribe(
        Request $request,
        EntityManagerInterface $em,
        NewsletterSubscriberRepository $repository,
        ValidatorInterface $validator,
        CsrfTokenManagerInterface $csrfTokenManager,
    ): Response {
        $payload = $request->request->all('newsletter');
        $token = $payload['_token'] ?? '';
        $email = trim($payload['email'] ?? '');

        if (!$csrfTokenManager->isTokenValid(new CsrfToken('newsletter', $token))) {
            $this->addFlash('danger', 'Jeton de sécurité invalide. Merci de réessayer.');
            return $this->redirectBack($request);
        }

        $violations = $validator->validate($email, [
            new Assert\NotBlank(message: 'Email requis.'),
            new Assert\Email(message: 'Email invalide.'),
        ]);

        if (\count($violations) > 0) {
            $this->addFlash('danger', $violations[0]->getMessage());
            return $this->redirectBack($request);
        }

        if ($repository->findOneBy(['email' => $email])) {
            $this->addFlash('info', 'Vous êtes déjà inscrit à la newsletter. Merci !');
            return $this->redirectBack($request);
        }

        $subscriber = (new NewsletterSubscriber())->setEmail($email);
        $em->persist($subscriber);
        $em->flush();

        $this->addFlash('success', 'Merci pour votre inscription ! Vous recevrez bientôt mes actualités.');
        return $this->redirectBack($request);
    }

    private function redirectBack(Request $request): Response
    {
        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }
}
