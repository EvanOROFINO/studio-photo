<?php

namespace App\Service;

use App\Entity\Booking;
use App\Entity\ContactRequest;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Aggregates KPIs and chart data for the admin dashboard.
 */
class DashboardStatsService
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /** @return array<string, int|float> */
    public function getKpis(): array
    {
        return [
            'totalPhotos' => (int) $this->scalar('SELECT COUNT(p) FROM App\Entity\Photo p'),
            'featuredPhotos' => (int) $this->scalar('SELECT COUNT(p) FROM App\Entity\Photo p WHERE p.featured = true'),
            'totalBookings' => (int) $this->scalar('SELECT COUNT(b) FROM App\Entity\Booking b'),
            'paidBookings' => (int) $this->scalar(
                'SELECT COUNT(b) FROM App\Entity\Booking b WHERE b.status IN (:s)',
                ['s' => [Booking::STATUS_PAID, Booking::STATUS_CONFIRMED]],
            ),
            'totalRevenue' => (float) $this->scalar(
                'SELECT COALESCE(SUM(b.depositAmount), 0) FROM App\Entity\Booking b WHERE b.status IN (:s)',
                ['s' => [Booking::STATUS_PAID, Booking::STATUS_CONFIRMED]],
            ),
            'newMessages' => (int) $this->scalar(
                'SELECT COUNT(c) FROM App\Entity\ContactRequest c WHERE c.status = :s',
                ['s' => ContactRequest::STATUS_NEW],
            ),
            'totalArticles' => (int) $this->scalar('SELECT COUNT(a) FROM App\Entity\Article a WHERE a.published = true'),
            'totalArticleViews' => (int) $this->scalar('SELECT COALESCE(SUM(a.viewCount), 0) FROM App\Entity\Article a'),
            'newsletterSubscribers' => (int) $this->scalar('SELECT COUNT(s) FROM App\Entity\NewsletterSubscriber s WHERE s.active = true'),
            'clientGalleries' => (int) $this->scalar('SELECT COUNT(g) FROM App\Entity\ClientGallery g WHERE g.active = true'),
        ];
    }

    /**
     * Bookings count per month over the last N months.
     * @return array{labels: string[], paid: int[], pending: int[]}
     */
    public function getBookingsPerMonth(int $months = 6): array
    {
        $labels = [];
        $paid = [];
        $pending = [];

        $now = new \DateTimeImmutable('first day of this month');

        for ($i = $months - 1; $i >= 0; $i--) {
            $start = $now->modify("-{$i} months");
            $end = $start->modify('+1 month');

            $labels[] = $this->formatMonthLabel($start);

            $paidCount = (int) $this->scalar(
                'SELECT COUNT(b) FROM App\Entity\Booking b WHERE b.status IN (:paid) AND b.createdAt >= :from AND b.createdAt < :to',
                ['paid' => [Booking::STATUS_PAID, Booking::STATUS_CONFIRMED], 'from' => $start, 'to' => $end],
            );
            $pendingCount = (int) $this->scalar(
                'SELECT COUNT(b) FROM App\Entity\Booking b WHERE b.status = :s AND b.createdAt >= :from AND b.createdAt < :to',
                ['s' => Booking::STATUS_PENDING, 'from' => $start, 'to' => $end],
            );

            $paid[] = $paidCount;
            $pending[] = $pendingCount;
        }

        return ['labels' => $labels, 'paid' => $paid, 'pending' => $pending];
    }

    /**
     * Revenue (deposit amounts) per month over the last N months.
     * @return array{labels: string[], values: float[]}
     */
    public function getRevenuePerMonth(int $months = 6): array
    {
        $labels = [];
        $values = [];
        $now = new \DateTimeImmutable('first day of this month');

        for ($i = $months - 1; $i >= 0; $i--) {
            $start = $now->modify("-{$i} months");
            $end = $start->modify('+1 month');

            $labels[] = $this->formatMonthLabel($start);
            $values[] = (float) $this->scalar(
                'SELECT COALESCE(SUM(b.depositAmount), 0) FROM App\Entity\Booking b WHERE b.status IN (:s) AND b.paidAt >= :from AND b.paidAt < :to',
                ['s' => [Booking::STATUS_PAID, Booking::STATUS_CONFIRMED], 'from' => $start, 'to' => $end],
            );
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Top services by booking count.
     * @return array{labels: string[], values: int[]}
     */
    public function getTopServices(int $limit = 5): array
    {
        $rows = $this->em->createQuery(
            'SELECT s.title AS title, COUNT(b.id) AS n
             FROM App\Entity\Booking b
             JOIN b.service s
             GROUP BY s.id
             ORDER BY n DESC',
        )->setMaxResults($limit)->getResult();

        return [
            'labels' => array_map(fn ($r) => $r['title'], $rows),
            'values' => array_map(fn ($r) => (int) $r['n'], $rows),
        ];
    }

    /**
     * Photos count per gallery category.
     * @return array{labels: string[], values: int[]}
     */
    public function getPhotosByCategory(): array
    {
        $rows = $this->em->createQuery(
            'SELECT c.name AS name, COUNT(p.id) AS n
             FROM App\Entity\Category c
             LEFT JOIN c.photos p
             GROUP BY c.id
             ORDER BY n DESC',
        )->getResult();

        return [
            'labels' => array_map(fn ($r) => $r['name'], $rows),
            'values' => array_map(fn ($r) => (int) $r['n'], $rows),
        ];
    }

    /**
     * Most viewed articles.
     * @return array{labels: string[], values: int[]}
     */
    public function getTopArticles(int $limit = 5): array
    {
        $rows = $this->em->createQuery(
            'SELECT a.title AS title, a.viewCount AS views
             FROM App\Entity\Article a
             WHERE a.published = true
             ORDER BY a.viewCount DESC',
        )->setMaxResults($limit)->getResult();

        return [
            'labels' => array_map(fn ($r) => mb_strlen($r['title']) > 40 ? mb_substr($r['title'], 0, 40).'…' : $r['title'], $rows),
            'values' => array_map(fn ($r) => (int) $r['views'], $rows),
        ];
    }

    /**
     * Contact requests grouped by project type.
     * @return array{labels: string[], values: int[]}
     */
    public function getContactRequestsByType(): array
    {
        $rows = $this->em->createQuery(
            'SELECT c.projectType AS type, COUNT(c.id) AS n
             FROM App\Entity\ContactRequest c
             GROUP BY c.projectType
             ORDER BY n DESC',
        )->getResult();

        $labelLookup = array_flip(ContactRequest::TYPES);

        return [
            'labels' => array_map(fn ($r) => $labelLookup[$r['type']] ?? ucfirst((string) $r['type']), $rows),
            'values' => array_map(fn ($r) => (int) $r['n'], $rows),
        ];
    }

    // -- helpers ---------------------------------------------------------

    private function scalar(string $dql, array $params = []): mixed
    {
        $q = $this->em->createQuery($dql);
        foreach ($params as $k => $v) {
            $q->setParameter($k, $v);
        }
        return $q->getSingleScalarResult();
    }

    private function formatMonthLabel(\DateTimeImmutable $date): string
    {
        $months = ['', 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sept', 'Oct', 'Nov', 'Déc'];
        return $months[(int) $date->format('n')].' '.$date->format('y');
    }
}
