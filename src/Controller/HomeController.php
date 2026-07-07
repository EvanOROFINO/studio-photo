<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\PhotoRepository;
use App\Repository\ServiceRepository;
use App\Repository\TestimonialRepository;
use App\Repository\VideoCategoryRepository;
use App\Repository\VideoRepository;
use App\Service\SiteContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(private readonly SiteContext $siteContext)
    {
    }

    #[Route('/', name: 'app_home')]
    public function index(
        PhotoRepository $photoRepository,
        CategoryRepository $categoryRepository,
        ServiceRepository $serviceRepository,
        TestimonialRepository $testimonialRepository,
        VideoRepository $videoRepository,
        VideoCategoryRepository $videoCategoryRepository,
    ): Response {
        $site = $this->siteContext->getCurrent();

        // Site Vidéo → home dédiée
        if ($this->siteContext->isVideo()) {
            return $this->render('home/video.html.twig', [
                'featuredVideos' => $videoRepository->findFeaturedForSite($site, 6),
                'categories' => $videoCategoryRepository->findActiveOrdered(),
                'services' => $serviceRepository->findActiveOrdered(),
                'testimonials' => $testimonialRepository->findPublishedOrdered(3, $site),
            ]);
        }

        // Site Photo (par défaut)
        return $this->render('home/index.html.twig', [
            'featuredPhotos' => $photoRepository->findFeatured(6),
            'categories' => $categoryRepository->findAllOrdered(),
            'services' => $serviceRepository->findActiveOrdered(),
            'testimonials' => $testimonialRepository->findPublishedOrdered(3, $site),
        ]);
    }

    #[Route('/a-propos', name: 'app_about')]
    public function about(): Response
    {
        // Mêmes infos but template peut différer selon le site
        $template = $this->siteContext->isVideo() && file_exists($this->getParameter('kernel.project_dir') . '/templates/home/about_video.html.twig')
            ? 'home/about_video.html.twig'
            : 'home/about.html.twig';

        return $this->render($template);
    }

    #[Route('/prestations', name: 'app_services')]
    public function services(ServiceRepository $serviceRepository): Response
    {
        return $this->render('home/services.html.twig', [
            'services' => $serviceRepository->findActiveOrdered(),
        ]);
    }
}
