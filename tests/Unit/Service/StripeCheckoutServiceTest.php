<?php

namespace App\Tests\Unit\Service;

use App\Service\StripeCheckoutService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeCheckoutServiceTest extends TestCase
{
    public function testDemoModeWhenSecretKeyIsEmpty(): void
    {
        $service = new StripeCheckoutService(
            secretKey: '',
            webhookSecret: '',
            urlGenerator: $this->createMock(UrlGeneratorInterface::class),
            studioName: 'Studio Test',
        );

        $this->assertFalse($service->isLiveMode());
    }

    public function testDemoModeWhenSecretKeyDoesNotStartWithSk(): void
    {
        $service = new StripeCheckoutService(
            secretKey: 'invalid_key_without_prefix',
            webhookSecret: '',
            urlGenerator: $this->createMock(UrlGeneratorInterface::class),
            studioName: 'Studio Test',
        );

        $this->assertFalse($service->isLiveMode());
    }

    public function testLiveModeWhenSecretKeyHasStripePrefix(): void
    {
        $service = new StripeCheckoutService(
            secretKey: 'sk_test_fakekey',
            webhookSecret: 'whsec_fake',
            urlGenerator: $this->createMock(UrlGeneratorInterface::class),
            studioName: 'Studio Test',
        );

        $this->assertTrue($service->isLiveMode());
    }
}
