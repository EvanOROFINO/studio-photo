<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\Booking;
use App\Entity\Category;
use App\Entity\ClientFilm;
use App\Entity\ClientGallery;
use App\Entity\ContactRequest;
use App\Entity\NewsletterSubscriber;
use App\Entity\Video;
use App\Entity\VideoCategory;
use App\Entity\VideoPackage;
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
            ->setTitle('Studio Photo/Vidéos — Administration')
            ->setFaviconPath('favicon.ico');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        // EasyAdmin 5 : linkToCrud() a disparu, on pointe désormais vers le CrudController.
        yield MenuItem::section('Site public');
        yield MenuItem::linkTo(PhotoCrudController::class, 'Photos', 'fa fa-image');
        yield MenuItem::linkTo(CategoryCrudController::class, 'Catégories', 'fa fa-folder');
        yield MenuItem::linkTo(TagCrudController::class, 'Tags', 'fa fa-hashtag');
        yield MenuItem::linkTo(ServiceCrudController::class, 'Prestations', 'fa fa-tag');
        yield MenuItem::linkTo(TestimonialCrudController::class, 'Témoignages', 'fa fa-star');
        yield MenuItem::linkTo(BeforeAfterCrudController::class, 'Avant / Après', 'fa fa-sliders-h');
        yield MenuItem::linkTo(VideoCrudController::class, 'Showreel / Vidéos', 'fa fa-video');
        yield MenuItem::linkTo(VideoCategoryCrudController::class, 'Catégories vidéo', 'fa fa-film');
        yield MenuItem::linkTo(VideoPackageCrudController::class, 'Forfaits vidéo', 'fa fa-clapperboard');

        yield MenuItem::section('Multi-site');
        yield MenuItem::linkTo(SiteCrudController::class, 'Sites (Photo / Vidéo)', 'fa fa-globe');

        yield MenuItem::section('Galeries clients');
        yield MenuItem::linkTo(ClientGalleryCrudController::class, 'Galeries privées', 'fa fa-lock');
        yield MenuItem::linkTo(ClientPhotoCrudController::class, 'Photos clients', 'fa fa-images');
        yield MenuItem::linkTo(ClientFilmCrudController::class, 'Films livrés', 'fa fa-film');

        yield MenuItem::section('Blog');
        yield MenuItem::linkTo(ArticleCrudController::class, 'Articles', 'fa fa-newspaper');
        yield MenuItem::linkTo(ArticleCategoryCrudController::class, 'Catégories blog', 'fa fa-tags');

        yield MenuItem::section('Réservations');
        yield MenuItem::linkTo(BookingCrudController::class, 'Réservations', 'fa fa-calendar-check');
        yield MenuItem::linkTo(BlockedDateCrudController::class, 'Dates bloquées', 'fa fa-calendar-times');

        yield MenuItem::section('Boutique');
        yield MenuItem::linkTo(ProductCrudController::class, 'Tirages / Produits', 'fa fa-tags');
        yield MenuItem::linkTo(OrderCrudController::class, 'Commandes', 'fa fa-shopping-bag');
        yield MenuItem::linkTo(CouponCrudController::class, 'Codes promo', 'fa fa-ticket-alt');

        yield MenuItem::section('Audience');
        yield MenuItem::linkTo(ContactRequestCrudController::class, 'Demandes de contact', 'fa fa-envelope');
        yield MenuItem::linkTo(NewsletterSubscriberCrudController::class, 'Abonnés newsletter', 'fa fa-bell');

        yield MenuItem::section('Système');
        yield MenuItem::linkTo(UserCrudController::class, 'Utilisateurs', 'fa fa-user');
        yield MenuItem::linkToUrl('Voir le site', 'fa fa-external-link-alt', '/')->setLinkTarget('_blank');
        yield MenuItem::linkToLogout('Déconnexion', 'fa fa-sign-out-alt');
    }
}
