<?php

namespace App\Controller;

use App\Repository\VideoCategoryRepository;
use App\Repository\VideoRepository;
use App\Service\SiteContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ShowreelController extends AbstractController
{
    public function __construct(private readonly SiteContext $siteContext)
    {
    }

    #[Route('/showreel', name: 'app_showreel')]
    public function index(
        Request $request,
        VideoRepository $repository,
        VideoCategoryRepository $categoryRepository,
    ): Response {
        $site = $this->siteContext->getCurrent();
        $categories = $categoryRepository->findActiveOrdered();

        $activeCategory = null;
        $categorySlug = $request->query->get('category');
        if ($categorySlug) {
            $activeCategory = $categoryRepository->findBySlug($categorySlug);
        }

        // Filtre par site (video) ; sur le site photo, on garde le comportement historique
        if ($this->siteContext->isVideo()) {
            $videos = $repository->findForSite($site, $activeCategory);
        } else {
            $videos = $repository->findPublishedOrdered();
        }

        return $this->render('showreel/index.html.twig', [
            'videos' => $videos,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
        ]);
    }

    #[Route('/showreel/{id}', name: 'app_video_detail', requirements: ['id' => '\d+'])]
    public function detail(int $id, VideoRepository $repository): Response
    {
        $video = $repository->find($id);
        if (!$video || !$video->isPublished()) {
            throw $this->createNotFoundException('Vidéo introuvable.');
        }

        // Vidéos similaires : même catégorie, même site, hors la courante
        $related = [];
        if ($video->getCategory() && $video->getSite()) {
            $related = array_filter(
                $repository->findForSite($video->getSite(), $video->getCategory()),
                fn ($v) => $v->getId() !== $video->getId()
            );
            $related = array_slice($related, 0, 3);
        }

        return $this->render('showreel/detail.html.twig', [
            'video' => $video,
            'related' => $related,
        ]);
    }
}
