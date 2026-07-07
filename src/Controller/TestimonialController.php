<?php

namespace App\Controller;

use App\Repository\TestimonialRepository;
use App\Service\SiteContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestimonialController extends AbstractController
{
    public function __construct(private readonly SiteContext $siteContext)
    {
    }

    #[Route('/temoignages', name: 'app_testimonials')]
    public function index(TestimonialRepository $testimonialRepository): Response
    {
        return $this->render('testimonials/index.html.twig', [
            'testimonials' => $testimonialRepository->findPublishedOrdered(null, $this->siteContext->getCurrent()),
        ]);
    }
}
