<?php

namespace App\Controller;

use App\Repository\VideoPackageRepository;
use App\Service\SiteContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class VideoPackageController extends AbstractController
{
    public function __construct(private readonly SiteContext $siteContext)
    {
    }

    #[Route('/forfaits-video', name: 'app_video_packages')]
    public function index(VideoPackageRepository $repository): Response
    {
        return $this->render('video/packages.html.twig', [
            'packages' => $repository->findActiveForSite($this->siteContext->getCurrent()),
        ]);
    }
}
