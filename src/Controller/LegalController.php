<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LegalController extends AbstractController
{
    #[Route('/mentions-legales', name: 'app_legal_notice')]
    public function legalNotice(): Response
    {
        return $this->render('legal/notice.html.twig');
    }

    #[Route('/politique-de-confidentialite', name: 'app_privacy')]
    public function privacy(): Response
    {
        return $this->render('legal/privacy.html.twig');
    }

    #[Route('/faq', name: 'app_faq')]
    public function faq(): Response
    {
        return $this->render('legal/faq.html.twig');
    }
}
