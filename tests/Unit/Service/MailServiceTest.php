<?php

namespace App\Tests\Unit\Service;

use App\Entity\ContactRequest;
use App\Service\MailService;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

class MailServiceTest extends TestCase
{
    public function testEmailsAreSentFromTheConfiguredAddressAndRepliesGoToTheVisitor(): void
    {
        $sent = [];
        $mailer = new class($sent) implements MailerInterface {
            public function __construct(private array &$sent)
            {
            }

            public function send(RawMessage $message, ?\Symfony\Component\Mailer\Envelope $envelope = null): void
            {
                $this->sent[] = $message;
            }
        };

        $service = new MailService($mailer, new NullLogger(), 'photographe@mon-studio.fr', 'Mon Studio', 'no-reply@mon-studio.fr');

        $contact = (new ContactRequest())
            ->setFullName('Camille Martin')
            ->setEmail('camille@exemple.fr')
            ->setProjectType('mariage')
            ->setMessage('Bonjour, je souhaite un devis.');

        $service->sendContactReceivedToAdmin($contact);

        $this->assertCount(1, $sent, 'Un e-mail est transmis au mailer.');
        /** @var Email $email */
        $email = $sent[0];
        $this->assertSame('no-reply@mon-studio.fr', $email->getFrom()[0]->getAddress());
        $this->assertSame('Mon Studio', $email->getFrom()[0]->getName());
        $this->assertSame('photographe@mon-studio.fr', $email->getTo()[0]->getAddress());
        $this->assertSame('camille@exemple.fr', $email->getReplyTo()[0]->getAddress());
    }
}
