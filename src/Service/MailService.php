<?php

namespace App\Service;

use App\Entity\Booking;
use App\Entity\ContactRequest;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Centralised entrypoint for all outgoing emails. Silently no-ops when MAILER_DSN
 * is null:// so the app keeps working in development without a real SMTP.
 */
class MailService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly string $adminEmail,
        private readonly string $studioName,
    ) {
    }

    public function sendContactReceivedToClient(ContactRequest $contact): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@studio-photo.local', $this->studioName))
            ->to(new Address($contact->getEmail(), $contact->getFullName()))
            ->subject(sprintf('Votre demande a bien été reçue — %s', $this->studioName))
            ->htmlTemplate('emails/contact_received_client.html.twig')
            ->context(['contact' => $contact]);

        $this->send($email);
    }

    public function sendContactReceivedToAdmin(ContactRequest $contact): void
    {
        if (!$this->adminEmail) {
            return;
        }
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@studio-photo.local', $this->studioName))
            ->to($this->adminEmail)
            ->replyTo(new Address($contact->getEmail(), $contact->getFullName()))
            ->subject(sprintf('Nouvelle demande : %s', $contact->getFullName()))
            ->htmlTemplate('emails/contact_received_admin.html.twig')
            ->context(['contact' => $contact]);

        $this->send($email);
    }

    public function sendBookingConfirmation(Booking $booking): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@studio-photo.local', $this->studioName))
            ->to(new Address($booking->getClientEmail(), $booking->getClientName()))
            ->subject(sprintf('Réservation confirmée — %s', $booking->getReference()))
            ->htmlTemplate('emails/booking_confirmed.html.twig')
            ->context(['booking' => $booking]);

        $this->send($email);
    }

    private function send(TemplatedEmail $email): void
    {
        try {
            $this->mailer->send($email);
        } catch (\Throwable $e) {
            // Email transport may not be configured in dev / demo mode — log and move on.
            $this->logger->warning('Email could not be sent', [
                'subject' => $email->getSubject(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
