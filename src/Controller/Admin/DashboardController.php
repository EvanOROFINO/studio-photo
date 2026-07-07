<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\ArticleCategory;
use App\Entity\BeforeAfter;
use App\Entity\BlockedDate;
use App\Entity\Booking;
use App\Entity\Category;
use App\Entity\ClientFilm;
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
use App\Entity\VideoPackage;
use App\Entity\Site;
use App\Repository\ContactRequestRepository;
use App\Repository\PhotoRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function index(): Response
    {
        $paidBookings = $this->em->getRepository(Booking::class)
            ->findBy(['status' => Booking::STATUS_PAID]);

        $totalRevenue = 0.0;
        foreach ($paidBookings as $b) {
            $totalRevenue += (float) $b->getDepositAmount();
        }

        // --- KPIs --------------------------------------------------------
        $kpis = [
            'totalRevenue' => $totalRevenue,
            'paidBookings' => count($paidBookings),
            'newMessages' => $this->contactRequestRepository->count(['status' => ContactRequest::STATUS_NEW]),
            'totalPhotos' => $this->photoRepository->count([]),
            'featuredPhotos' => $this->photoRepository->count(['featured' => true]),
            'totalArticles' => $this->em->getRepository(Article::class)->count(['published' => true]),
            'totalArticleViews' => $this->sumArticleViews(),
            'newsletterSubscribers' => $this->em->getRepository(NewsletterSubscriber::class)->count([]),
            'clientGalleries' => $this->em->getRepository(ClientGallery::class)->count(['active' => true]),
        ];

        // --- KPIs Studio Vidéo ------------------------------------------
        $videoKpis = [
            'totalVideos' => $this->em->getRepository(Video::class)->count(['published' => true]),
            'videoPackages' => $this->em->getRepository(VideoPackage::class)->count(['isActive' => true]),
            'videoCategories' => $this->em->getRepository(VideoCategory::class)->count(['isActive' => true]),
            'deliveredFilms' => $this->em->getRepository(ClientFilm::class)->count([]),
        ];

        // --- Charts (aggregated in PHP for MySQL/SQLite portability) ----
        $allBookings = $this->em->getRepository(Booking::class)->findAll();

        return $this->render('admin/dashboard.html.twig', array_merge([
            'kpis' => $kpis,
            'videoKpis' => $videoKpis,
            'bookingsPerMonth' => $this->bookingsPerMonth($allBookings),
            'revenuePerMonth' => $this->revenuePerMonth($paidBookings),
            'topServices' => $this->topServices($allBookings),
            'contactsByType' => $this->contactsByType(),
            'photosByCategory' => $this->photosByCategory(),
            'topArticles' => $this->topArticles(),
        ]));
    }

    private function sumArticleViews(): int
    {
        $total = 0;
        foreach ($this->em->getRepository(Article::class)->findBy(['published' => true]) as $a) {
            $total += $a->getViewCount();
        }
        return $total;
    }

    /** @param Booking[] $bookings */
    private function bookingsPerMonth(array $bookings): array
    {
        $months = $this->lastMonths(6);
        $paid = array_fill_keys(array_keys($months), 0);
        $pending = array_fill_keys(array_keys($months), 0);

        foreach ($bookings as $b) {
            $key = ($b->getCreatedAt() ?? new \DateTimeImmutable())->format('Y-m');
            if (!isset($months[$key])) {
                continue;
            }
            if ($b->getStatus() === Booking::STATUS_PAID) {
                $paid[$key]++;
            } else {
                $pending[$key]++;
            }
        }

        return [
            'labels' => array_values($months),
            'paid' => array_values($paid),
            'pending' => array_values($pending),
        ];
    }

    /** @param Booking[] $paidBookings */
    private function revenuePerMonth(array $paidBookings): array
    {
        $months = $this->lastMonths(6);
        $revenue = array_fill_keys(array_keys($months), 0.0);

        foreach ($paidBookings as $b) {
            $key = ($b->getCreatedAt() ?? new \DateTimeImmutable())->format('Y-m');
            if (isset($revenue[$key])) {
                $revenue[$key] += (float) $b->getDepositAmount();
            }
        }

        return [
            'labels' => array_values($months),
            'values' => array_map(fn ($v) => round($v, 2), array_values($revenue)),
        ];
    }

    /** @param Booking[] $bookings */
    private function topServices(array $bookings): array
    {
        $counts = [];
        foreach ($bookings as $b) {
            $name = $b->getService()?->getTitle() ?? 'Autre';
            $counts[$name] = ($counts[$name] ?? 0) + 1;
        }
        arsort($counts);
        $counts = array_slice($counts, 0, 5, true);

        return [
            'labels' => array_keys($counts),
            'values' => array_values($counts),
        ];
    }

    private function contactsByType(): array
    {
        $counts = [];
        foreach ($this->em->getRepository(ContactRequest::class)->findAll() as $c) {
            $type = $c->getProjectType() ?? 'autre';
            $counts[$type] = ($counts[$type] ?? 0) + 1;
        }

        return [
            'labels' => array_keys($counts),
            'values' => array_values($counts),
        ];
    }

    private function photosByCategory(): array
    {
        $counts = [];
        foreach ($this->em->getRepository(Category::class)->findBy([], ['position' => 'ASC']) as $cat) {
            $counts[$cat->getName()] = $cat->getPhotos()->count();
        }

        return [
            'labels' => array_keys($counts),
            'values' => array_values($counts),
        ];
    }

    private function topArticles(): array
    {
        $articles = $this->em->getRepository(Article::class)
            ->findBy(['published' => true], ['viewCount' => 'DESC'], 5);

        return [
            'labels' => array_map(fn ($a) => mb_strimwidth((string) $a->getTitle(), 0, 30, '…'), $articles),
            'values' => array_map(fn ($a) => $a->getViewCount(), $articles),
        ];
    }

    /** @return array<string, string> Y-m => "M yy" for the last N months */
    private function lastMonths(int $n): array
    {
        $frMonths = ['', 'jan', 'fév', 'mar', 'avr', 'mai', 'juin', 'juil', 'août', 'sep', 'oct', 'nov', 'déc'];
        $months = [];
        $cursor = new \DateTimeImmutable('first day of this month');
        for ($i = $n - 1; $i >= 0; $i--) {
            $d = $cursor->modify("-$i month");
            $months[$d->format('Y-m')] = $frMonths[(int) $d->format('n')].' '.$d->format('y');
        }
        return $months;
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
        yield MenuItem::linkToCrud('Forfaits vidéo', 'fa fa-clapperboard', VideoPackage::class);

        yield MenuItem::section('Multi-site');
        yield MenuItem::linkToCrud('Sites (Photo / Vidéo)', 'fa fa-globe', Site::class);

        yield MenuItem::section('Galeries clients');
        yield MenuItem::linkToCrud('Galeries privées', 'fa fa-lock', ClientGallery::class);
        yield MenuItem::linkToCrud('Photos clients', 'fa fa-images', ClientPhoto::class);
        yield MenuItem::linkToCrud('Films livrés', 'fa fa-film', ClientFilm::class);

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
