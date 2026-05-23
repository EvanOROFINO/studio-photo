<?php

namespace App\Service;

use App\Entity\Booking;
use App\Repository\BlockedDateRepository;
use App\Repository\BookingRepository;

class AvailabilityService
{
    public function __construct(
        private readonly BookingRepository $bookingRepository,
        private readonly BlockedDateRepository $blockedDateRepository,
    ) {
    }

    /**
     * Returns a list of unavailable date events in the window, ready for FullCalendar.
     * @return array<int, array{title:string, start:string, end?:string, color:string, allDay:bool}>
     */
    public function getUnavailableEvents(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        $events = [];

        // 1) Confirmed/paid bookings — booked dates are off-limits
        $bookings = $this->bookingRepository->createQueryBuilder('b')
            ->andWhere('b.status IN (:statuses)')
            ->andWhere('b.eventDate BETWEEN :from AND :to')
            ->setParameter('statuses', [Booking::STATUS_PAID, Booking::STATUS_CONFIRMED])
            ->setParameter('from', $from->setTime(0, 0, 0))
            ->setParameter('to', $to->setTime(23, 59, 59))
            ->getQuery()
            ->getResult();

        foreach ($bookings as $booking) {
            $events[] = [
                'title' => 'Réservé',
                'start' => $booking->getEventDate()->format('Y-m-d'),
                'color' => '#dc3545',
                'allDay' => true,
            ];
        }

        // 2) Blocked dates (vacations, training, personal)
        $blocked = $this->blockedDateRepository->findOverlapping($from, $to);
        foreach ($blocked as $b) {
            $end = $b->getEffectiveEndDate()->modify('+1 day'); // FullCalendar end is exclusive
            $events[] = [
                'title' => 'Indisponible',
                'start' => $b->getStartDate()->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
                'color' => '#6c757d',
                'allDay' => true,
            ];
        }

        return $events;
    }

    /** Returns true if the given date is bookable. */
    public function isAvailable(\DateTimeImmutable $date): bool
    {
        // Past dates are never available
        $today = new \DateTimeImmutable('today');
        if ($date < $today) {
            return false;
        }

        // Conflict with paid/confirmed booking? (match by day, ignoring time)
        $dayStart = $date->setTime(0, 0, 0);
        $dayEnd = $date->setTime(23, 59, 59);
        $clashingBooking = $this->bookingRepository->createQueryBuilder('b')
            ->andWhere('b.status IN (:statuses)')
            ->andWhere('b.eventDate BETWEEN :dayStart AND :dayEnd')
            ->setParameter('statuses', [Booking::STATUS_PAID, Booking::STATUS_CONFIRMED])
            ->setParameter('dayStart', $dayStart)
            ->setParameter('dayEnd', $dayEnd)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($clashingBooking) {
            return false;
        }

        // Conflict with blocked date?
        $blocked = $this->blockedDateRepository->findOverlapping($date, $date);
        if (!empty($blocked)) {
            return false;
        }

        return true;
    }
}
