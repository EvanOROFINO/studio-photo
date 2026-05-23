<?php

namespace App\Service;

use App\Entity\Booking;
use Stripe\StripeClient;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Wraps Stripe Checkout for booking deposits.
 *
 * Falls back to "demo mode" when STRIPE_SECRET_KEY is not configured: the checkout
 * step skips Stripe entirely and the booking is marked as paid immediately, so the
 * full workflow remains testable without a Stripe account.
 */
class StripeCheckoutService
{
    public function __construct(
        private readonly string $secretKey,
        private readonly string $webhookSecret,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $studioName,
    ) {
    }

    public function isLiveMode(): bool
    {
        return $this->secretKey !== '' && str_starts_with($this->secretKey, 'sk_');
    }

    /**
     * Create a Stripe Checkout Session for the booking's deposit.
     * In demo mode, returns null — the controller redirects straight to success.
     */
    public function createCheckoutSession(Booking $booking): ?array
    {
        if (!$this->isLiveMode()) {
            return null;
        }

        $stripe = new StripeClient($this->secretKey);

        $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'customer_email' => $booking->getClientEmail(),
            'client_reference_id' => $booking->getReference(),
            'metadata' => [
                'booking_reference' => $booking->getReference(),
            ],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => (int) round(((float) $booking->getDepositAmount()) * 100),
                    'product_data' => [
                        'name' => sprintf('Acompte — %s', $booking->getService()?->getTitle() ?? 'Réservation'),
                        'description' => sprintf(
                            '%s • Acompte 30%% sur %s €',
                            $this->studioName,
                            number_format((float) $booking->getAmountTotal(), 0, ',', ' '),
                        ),
                    ],
                ],
                'quantity' => 1,
            ]],
            'success_url' => $this->urlGenerator->generate(
                'app_booking_success',
                ['reference' => $booking->getReference(), 'session_id' => '{CHECKOUT_SESSION_ID}'],
                UrlGeneratorInterface::ABSOLUTE_URL,
            ),
            'cancel_url' => $this->urlGenerator->generate(
                'app_booking_cancel',
                ['reference' => $booking->getReference()],
                UrlGeneratorInterface::ABSOLUTE_URL,
            ),
        ]);

        return [
            'id' => $session->id,
            'url' => $session->url,
            'payment_intent' => $session->payment_intent,
        ];
    }

    public function retrieveSession(string $sessionId): ?array
    {
        if (!$this->isLiveMode()) {
            return null;
        }
        $stripe = new StripeClient($this->secretKey);
        $session = $stripe->checkout->sessions->retrieve($sessionId);
        return [
            'payment_status' => $session->payment_status,
            'payment_intent' => $session->payment_intent,
            'amount_total' => $session->amount_total,
        ];
    }

    /**
     * Verify and parse a Stripe webhook event. Returns null when in demo mode
     * or when the signature is invalid.
     */
    public function parseWebhook(Request $request): ?array
    {
        if (!$this->isLiveMode() || $this->webhookSecret === '') {
            return null;
        }
        $signature = (string) $request->headers->get('Stripe-Signature', '');
        try {
            $event = Webhook::constructEvent($request->getContent(), $signature, $this->webhookSecret);
        } catch (\Throwable) {
            return null;
        }
        return [
            'type' => $event->type,
            'data' => $event->data->object,
        ];
    }
}
