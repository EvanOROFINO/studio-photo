<?php

namespace App\Tests\Functional;

use App\Entity\BlockedDate;
use App\Entity\Booking;
use App\Service\AvailabilityService;
use App\Tests\AbstractAppWebTestCase;

class AvailabilityServiceTest extends AbstractAppWebTestCase
{
    private function service(): AvailabilityService
    {
        return static::getContainer()->get(AvailabilityService::class);
    }

    public function testPastDateIsNeverAvailable(): void
    {
        $yesterday = new \DateTimeImmutable('-1 day');
        $this->assertFalse($this->service()->isAvailable($yesterday));
    }

    public function testFreeFutureDateIsAvailable(): void
    {
        // Pick a far-out date with no blocking
        $farFuture = new \DateTimeImmutable('+200 days');
        $this->assertTrue($this->service()->isAvailable($farFuture));
    }

    public function testBlockedDateIsNotAvailable(): void
    {
        $blocked = new BlockedDate();
        $blocked->setStartDate(new \DateTimeImmutable('+5 days'));
        $blocked->setReason('Test block');
        $this->em->persist($blocked);
        $this->em->flush();

        $date = new \DateTimeImmutable('+5 days');
        $this->assertFalse($this->service()->isAvailable($date));
    }

    public function testPaidBookingDateIsNotAvailable(): void
    {
        $service = $this->em->getRepository(\App\Entity\Service::class)->findOneBy([]);
        $this->assertNotNull($service, 'A service must exist in fixtures');

        $booking = new Booking();
        $booking->setService($service);
        $booking->setClientName('Test Client');
        $booking->setClientEmail('test@example.fr');
        $booking->setEventDate(new \DateTimeImmutable('+15 days'));
        $booking->setAmountTotal('1000');
        $booking->setDepositAmount('300');
        $booking->setStatus(Booking::STATUS_PAID);
        $this->em->persist($booking);
        $this->em->flush();

        $this->assertFalse($this->service()->isAvailable(new \DateTimeImmutable('+15 days')));
    }

    public function testGetUnavailableEventsReturnsBothBookingsAndBlocks(): void
    {
        $blocked = new BlockedDate();
        $blocked->setStartDate(new \DateTimeImmutable('+7 days'));
        $blocked->setReason('Test');
        $this->em->persist($blocked);
        $this->em->flush();

        $events = $this->service()->getUnavailableEvents(
            new \DateTimeImmutable('today'),
            new \DateTimeImmutable('+30 days'),
        );

        $this->assertGreaterThanOrEqual(1, count($events));
        $titles = array_column($events, 'title');
        $this->assertContains('Indisponible', $titles);
    }
}
