<?php

namespace App\Controller;

use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ShowreelController extends AbstractController
{
    #[Route('/showreel', name: 'app_showreel')]
    public function index(VideoRepository $repository): Response
    {
        return $this->render('showreel/index.html.twig', [
            'videos' => $repository->findPublishedOrdered(),
        ]);
    }
}
