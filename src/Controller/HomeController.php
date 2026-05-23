<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\PhotoRepository;
use App\Repository\ServiceRepository;
use App\Repository\TestimonialRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        PhotoRepository $photoRepository,
        CategoryRepository $categoryRepository,
        ServiceRepository $serviceRepository,
        TestimonialRepository $testimonialRepository,
    ): Response {
        return $this->render('home/index.html.twig', [
            'featuredPhotos' => $photoRepository->findFeatured(6),
            'categories' => $categoryRepository->findAllOrdered(),
            'services' => $serviceRepository->findActiveOrdered(),
            'testimonials' => $testimonialRepository->findPublishedOrdered(3),
        ]);
    }

    #[Route('/a-propos', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('home/about.html.twig');
    }

    #[Route('/prestations', name: 'app_services')]
    public function services(ServiceRepository $serviceRepository): Response
    {
        return $this->render('home/services.html.twig', [
            'services' => $serviceRepository->findActiveOrdered(),
        ]);
    }
}
