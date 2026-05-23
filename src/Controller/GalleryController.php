<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Photo;
use App\Repository\CategoryRepository;
use App\Repository\PhotoRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GalleryController extends AbstractController
{
    private const PHOTOS_PER_PAGE = 24;

    #[Route('/galerie', name: 'app_gallery')]
    public function index(
        Request $request,
        CategoryRepository $categoryRepository,
        PhotoRepository $photoRepository,
    ): Response {
        $page = max(1, $request->query->getInt('page', 1));
        $paginator = $photoRepository->paginate($page, self::PHOTOS_PER_PAGE);
        $totalPages = (int) ceil(count($paginator) / self::PHOTOS_PER_PAGE);

        return $this->render('gallery/index.html.twig', [
            'categories' => $categoryRepository->findAllOrdered(),
            'photos' => $paginator,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalPhotos' => count($paginator),
        ]);
    }

    #[Route('/galerie/{slug}', name: 'app_gallery_category')]
    public function category(
        #[MapEntity(mapping: ['slug' => 'slug'])] Category $category,
        PhotoRepository $photoRepository,
        CategoryRepository $categoryRepository,
    ): Response {
        return $this->render('gallery/category.html.twig', [
            'category' => $category,
            'photos' => $photoRepository->findByCategory($category),
            'categories' => $categoryRepository->findAllOrdered(),
        ]);
    }

    #[Route('/photo/{id}', name: 'app_photo_show', requirements: ['id' => '\d+'])]
    public function show(Photo $photo, PhotoRepository $photoRepository): Response
    {
        return $this->render('gallery/show.html.twig', [
            'photo' => $photo,
            'previous' => $photoRepository->findPrevious($photo),
            'next' => $photoRepository->findNext($photo),
        ]);
    }
}
