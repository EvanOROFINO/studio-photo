<?php

namespace App\Controller;

use App\Repository\ArticleCategoryRepository;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\PhotoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SitemapController extends AbstractController
{
    #[Route('/sitemap', name: 'app_sitemap')]
    public function sitemap(
        CategoryRepository $categoryRepository,
        PhotoRepository $photoRepository,
        ArticleRepository $articleRepository,
        ArticleCategoryRepository $articleCategoryRepository,
    ): Response {
        $urls = [
            ['route' => 'app_home', 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['route' => 'app_gallery', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['route' => 'app_services', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['route' => 'app_availability', 'priority' => '0.7', 'changefreq' => 'daily'],
            ['route' => 'app_blog', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['route' => 'app_about', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['route' => 'app_testimonials', 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['route' => 'app_contact', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['route' => 'app_faq', 'priority' => '0.4', 'changefreq' => 'monthly'],
            ['route' => 'app_legal_notice', 'priority' => '0.2', 'changefreq' => 'yearly'],
            ['route' => 'app_privacy', 'priority' => '0.2', 'changefreq' => 'yearly'],
        ];

        $response = $this->render('sitemap/sitemap.xml.twig', [
            'urls' => $urls,
            'categories' => $categoryRepository->findAllOrdered(),
            'photos' => $photoRepository->findAll(),
            'articles' => $articleRepository->findAllPublished(),
            'articleCategories' => $articleCategoryRepository->findAllOrdered(),
        ]);
        $response->headers->set('Content-Type', 'application/xml');
        return $response;
    }
}
