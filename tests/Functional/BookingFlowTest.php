<?php

namespace App\Tests\Functional;

use App\Entity\Booking;
use App\Entity\Service;
use App\Tests\AbstractAppWebTestCase;

class BookingFlowTest extends AbstractAppWebTestCase
{
    private function firstService(): Service
    {
        $repo = $this->em->getRepository(Service::class);
        $all = $repo->findAll();
        fwrite(STDERR, "\n[DEBUG] Services in DB: ".count($all).PHP_EOL);
        $service = $repo->findOneBy([]);
        $this->assertNotNull($service, sprintf('A service must exist in fixtures (found %d total)', count($all)));
        return $service;
    }

    public function testBookingFormRendersWithServicePrice(): void
    {
        $service = $this->firstService();
        $this->client->request('GET', '/reservation/service/'.$service->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $service->getTitle());
        $this->assertSelectorExists('input[name="booking[clientName]"]');
        $this->assertSelectorExists('input[name="booking[eventDate]"]');
    }

    public function testBookingOnPastDateIsRejectedByServerValidation(): void
    {
        // The Service::isAvailable() server-side check should refuse past dates.
        // We test it directly here since the HTML form's date input gets a
        // browser-side `min` attribute that BrowserKit doesn't enforce.
        /** @var \App\Service\AvailabilityService $availability */
        $availability = static::getContainer()->get(\App\Service\AvailabilityService::class);
        $this->assertFalse($availability->isAvailable(new \DateTimeImmutable('-5 days')));
        $this->assertFalse($availability->isAvailable(new \DateTimeImmutable('yesterday')));
    }

    public function testReservationCreatedInDemoModeIsMarkedPaid(): void
    {
        $service = $this->firstService();
        $crawler = $this->client->request('GET', '/reservation/service/'.$service->getId());

        $buttonNode = $crawler->filter('button[type="submit"]')->first();
        $form = $buttonNode->form([
            'booking[clientName]' => 'Demo Tester',
            'booking[clientEmail]' => 'demo@example.fr',
            'booking[clientPhone]' => '',
            'booking[eventDate]' => (new \DateTimeImmutable('+30 days'))->format('Y-m-d'),
            'booking[location]' => 'Lyon',
            'booking[notes]' => '',
        ]);

        $this->client->followRedirects(true);
        $this->client->submit($form);

        $this->em->clear();
        $booking = $this->em->getRepository(Booking::class)->findOneBy(['clientEmail' => 'demo@example.fr']);
        $this->assertNotNull($booking, 'A booking should be created');
        $this->assertSame(Booking::STATUS_PAID, $booking->getStatus(), 'Demo mode should mark booking as paid');
        $this->assertNotNull($booking->getPaidAt());
    }
}
