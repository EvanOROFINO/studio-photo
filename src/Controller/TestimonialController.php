<?php

namespace App\Controller;

use App\Repository\TestimonialRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestimonialController extends AbstractController
{
    #[Route('/temoignages', name: 'app_testimonials')]
    public function index(TestimonialRepository $testimonialRepository): Response
    {
        return $this->render('testimonials/index.html.twig', [
            'testimonials' => $testimonialRepository->findPublishedOrdered(),
        ]);
    }
}
