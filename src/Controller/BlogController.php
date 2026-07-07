<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\ArticleCategory;
use App\Repository\ArticleCategoryRepository;
use App\Repository\ArticleRepository;
use App\Service\SiteContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    private const ARTICLES_PER_PAGE = 9;

    public function __construct(private readonly SiteContext $siteContext)
    {
    }

    #[Route('/blog', name: 'app_blog')]
    public function index(
        Request $request,
        ArticleRepository $articleRepository,
        ArticleCategoryRepository $categoryRepository,
    ): Response {
        $site = $this->siteContext->getCurrent();
        $page = max(1, $request->query->getInt('page', 1));
        $paginator = $articleRepository->paginatePublished($page, self::ARTICLES_PER_PAGE, $site);
        $totalPages = (int) ceil(count($paginator) / self::ARTICLES_PER_PAGE);

        return $this->render('blog/index.html.twig', [
            'articles' => $paginator,
            'categories' => $categoryRepository->findAllOrdered(),
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => count($paginator),
            'currentCategory' => null,
        ]);
    }

    #[Route('/blog/categorie/{slug}', name: 'app_blog_category')]
    public function category(
        #[MapEntity(mapping: ['slug' => 'slug'])] ArticleCategory $category,
        Request $request,
        ArticleRepository $articleRepository,
        ArticleCategoryRepository $categoryRepository,
    ): Response {
        $site = $this->siteContext->getCurrent();
        $page = max(1, $request->query->getInt('page', 1));
        $paginator = $articleRepository->paginateByCategory($category, $page, self::ARTICLES_PER_PAGE, $site);
        $totalPages = (int) ceil(count($paginator) / self::ARTICLES_PER_PAGE);

        return $this->render('blog/index.html.twig', [
            'articles' => $paginator,
            'categories' => $categoryRepository->findAllOrdered(),
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => count($paginator),
            'currentCategory' => $category,
        ]);
    }

    #[Route('/blog/{slug}', name: 'app_blog_show', requirements: ['slug' => '[a-z0-9-]+'])]
    public function show(
        string $slug,
        ArticleRepository $articleRepository,
        ArticleCategoryRepository $categoryRepository,
        EntityManagerInterface $em,
    ): Response {
        $article = $articleRepository->findOnePublishedBySlug($slug);
        if (!$article) {
            throw $this->createNotFoundException();
        }

        $article->incrementViewCount();
        $em->flush();

        return $this->render('blog/show.html.twig', [
            'article' => $article,
            'recentArticles' => $articleRepository->findRecent(4, $article),
            'categories' => $categoryRepository->findAllOrdered(),
        ]);
    }

    #[Route('/feed', name: 'app_blog_feed')]
    public function feed(ArticleRepository $articleRepository): Response
    {
        $articles = array_slice($articleRepository->findAllPublished(), 0, 20);
        $response = $this->render('blog/feed.xml.twig', ['articles' => $articles]);
        $response->headers->set('Content-Type', 'application/rss+xml; charset=utf-8');
        return $response;
    }
}
