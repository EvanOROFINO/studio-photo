<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\ArticleCategory;
use App\Entity\BeforeAfter;
use App\Entity\BlockedDate;
use App\Entity\Booking;
use App\Entity\Category;
use App\Entity\ClientGallery;
use App\Entity\ClientPhoto;
use App\Entity\ContactRequest;
use App\Entity\NewsletterSubscriber;
use App\Entity\Order;
use App\Entity\Photo;
use App\Entity\Product;
use App\Entity\Service;
use App\Entity\Tag;
use App\Entity\Testimonial;
use App\Entity\User;
use App\Entity\Video;
use App\Entity\VideoCategory;
use App\Entity\Site;
use App\Repository\ContactRequestRepository;
use App\Repository\PhotoRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly PhotoRepository $photoRepository,
        private readonly ContactRequestRepository $contactRequestRepository,
    ) {
    }

    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'totalPhotos' => $this->photoRepository->count([]),
            'featuredPhotos' => $this->photoRepository->count(['featured' => true]),
            'newMessages' => $this->contactRequestRepository->count(['status' => ContactRequest::STATUS_NEW]),
            'totalMessages' => $this->contactRequestRepository->count([]),
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Studio Photo — Administration')
            ->setFaviconPath('favicon.ico');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        yield MenuItem::section('Site public');
        yield MenuItem::linkToCrud('Photos', 'fa fa-image', Photo::class);
        yield MenuItem::linkToCrud('Catégories', 'fa fa-folder', Category::class);
        yield MenuItem::linkToCrud('Tags', 'fa fa-hashtag', Tag::class);
        yield MenuItem::linkToCrud('Prestations', 'fa fa-tag', Service::class);
        yield MenuItem::linkToCrud('Témoignages', 'fa fa-star', Testimonial::class);
        yield MenuItem::linkToCrud('Avant / Après', 'fa fa-sliders-h', BeforeAfter::class);
        yield MenuItem::linkToCrud('Showreel / Vidéos', 'fa fa-video', Video::class);
        yield MenuItem::linkToCrud('Catégories vidéo', 'fa fa-film', VideoCategory::class);

        yield MenuItem::section('Multi-site');
        yield MenuItem::linkToCrud('Sites (Photo / Vidéo)', 'fa fa-globe', Site::class);

        yield MenuItem::section('Galeries clients');
        yield MenuItem::linkToCrud('Galeries privées', 'fa fa-lock', ClientGallery::class);
        yield MenuItem::linkToCrud('Photos clients', 'fa fa-images', ClientPhoto::class);

        yield MenuItem::section('Blog');
        yield MenuItem::linkToCrud('Articles', 'fa fa-newspaper', Article::class);
        yield MenuItem::linkToCrud('Catégories blog', 'fa fa-tags', ArticleCategory::class);

        yield MenuItem::section('Réservations');
        yield MenuItem::linkToCrud('Réservations', 'fa fa-calendar-check', Booking::class);
        yield MenuItem::linkToCrud('Dates bloquées', 'fa fa-calendar-times', BlockedDate::class);

        yield MenuItem::section('Boutique');
        yield MenuItem::linkToCrud('Tirages / Produits', 'fa fa-tags', Product::class);
        yield MenuItem::linkToCrud('Commandes', 'fa fa-shopping-bag', Order::class);
        yield MenuItem::linkToCrud('Codes promo', 'fa fa-ticket-alt', \App\Entity\Coupon::class);

        yield MenuItem::section('Audience');
        yield MenuItem::linkToCrud('Demandes de contact', 'fa fa-envelope', ContactRequest::class);
        yield MenuItem::linkToCrud('Abonnés newsletter', 'fa fa-bell', NewsletterSubscriber::class);

        yield MenuItem::section('Système');
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-user', User::class);
        yield MenuItem::linkToUrl('Voir le site', 'fa fa-external-link-alt', '/')->setLinkTarget('_blank');
        yield MenuItem::linkToLogout('Déconnexion', 'fa fa-sign-out-alt');
    }
}
