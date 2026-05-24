<?php

namespace App\Controller;

use App\Repository\BeforeAfterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BeforeAfterController extends AbstractController
{
    #[Route('/avant-apres', name: 'app_before_after')]
    public function index(BeforeAfterRepository $repository): Response
    {
        return $this->render('before_after/index.html.twig', [
            'items' => $repository->findPublishedOrdered(),
        ]);
    }
}
