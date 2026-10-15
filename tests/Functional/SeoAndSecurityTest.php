<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Tests\AbstractAppWebTestCase;

/**
 * Éléments « de finition » vérifiés lors de l'audit du site : référencement (robots, sitemap, icône),
 * page 404 habillée, en-têtes de sécurité, lien WhatsApp valide, lien d'administration réservé à l'admin.
 */
class SeoAndSecurityTest extends AbstractAppWebTestCase
{
    public function testRobotsTxtAnnouncesAnAbsoluteSitemapUrl(): void
    {
        $this->client->request('GET', '/robots.txt');

        $this->assertResponseIsSuccessful();
        $this->assertStringStartsWith('text/plain', (string) $this->client->getResponse()->headers->get('Content-Type'));
        $content = (string) $this->client->getResponse()->getContent();
        $this->assertMatchesRegularExpression('#^Sitemap: https?://[^/\s]+/sitemap$#m', $content, 'Le sitemap doit être annoncé avec une URL absolue.');
        foreach (['Disallow: /admin', 'Disallow: /login', 'Disallow: /galerie-client'] as $rule) {
            $this->assertStringContainsString($rule, $content);
        }
    }

    public function testSitemapIsAlsoReachableAsSitemapXml(): void
    {
        $this->client->request('GET', '/sitemap.xml');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('<urlset', (string) $this->client->getResponse()->getContent());
    }

    public function testFaviconIcoRedirectsToTheSvgIcon(): void
    {
        $this->client->request('GET', '/favicon.ico');

        $this->assertResponseStatusCodeSame(301);
        $this->assertResponseRedirects('/favicon.svg');
    }

    public function testNotFoundPageIsBrandedWithSiteNavigation(): void
    {
        // En test (debug activé) Symfony affiche sa page de débogage : on rend directement le gabarit
        // que la production utilise pour les erreurs 404.
        $this->client->request('GET', '/');
        $requestStack = static::getContainer()->get('request_stack');
        $requestStack->push($this->client->getRequest());

        $html = static::getContainer()->get('twig')->render('@Twig/Exception/error404.html.twig', [
            'status_code' => 404,
            'status_text' => 'Not Found',
        ]);

        $this->assertStringContainsString('Page introuvable', $html);
        $this->assertStringContainsString('Retour à l\'accueil', $html);
        $this->assertStringContainsString('Galerie', $html, 'La 404 garde la navigation du site.');
        $this->assertStringContainsString('Me contacter', $html);
    }

    public function testResponsesCarryBasicSecurityHeaders(): void
    {
        $this->client->request('GET', '/');

        $headers = $this->client->getResponse()->headers;
        $this->assertSame('nosniff', $headers->get('X-Content-Type-Options'));
        $this->assertSame('SAMEORIGIN', $headers->get('X-Frame-Options'));
        $this->assertSame('strict-origin-when-cross-origin', $headers->get('Referrer-Policy'));
        $this->assertStringContainsString('camera=()', (string) $headers->get('Permissions-Policy'));
    }

    public function testWhatsappLinkUsesTheInternationalNumberFormat(): void
    {
        $this->client->request('GET', '/');

        $html = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString('https://wa.me/33612345678', $html);
        $this->assertStringNotContainsString('wa.me/0612', $html, 'Le 0 initial doit être remplacé par l\'indicatif 33.');
    }

    public function testShareImageIsLocalNotAThirdPartyHotlink(): void
    {
        foreach (['/', '/?_site=video'] as $url) {
            $this->client->request('GET', $url);
            $html = (string) $this->client->getResponse()->getContent();
            $this->assertMatchesRegularExpression('#<meta property="og:image" content="http://localhost/images/og-(photo|video)\.jpg">#', $html);
            $this->assertStringNotContainsString('unsplash.com', $html);
        }
    }

    public function testStudioDetailsCanBeSetFromEnvironmentVariablesWithoutTouchingTheCode(): void
    {
        $_SERVER['CONTACT_EMAIL'] = $_ENV['CONTACT_EMAIL'] = 'bonjour@mon-studio-test.fr';
        $_SERVER['CONTACT_PHONE'] = $_ENV['CONTACT_PHONE'] = '07 98 76 54 32';

        try {
            static::ensureKernelShutdown();
            $client = static::createClient();
            $client->request('GET', '/contact');

            $html = (string) $client->getResponse()->getContent();
            $this->assertStringContainsString('bonjour@mon-studio-test.fr', $html);
            $this->assertStringContainsString('07 98 76 54 32', $html);
            $this->assertStringContainsString('https://wa.me/33798765432', $html);
            $this->assertStringNotContainsString('studio-photo.local', $html);
        } finally {
            unset($_SERVER['CONTACT_EMAIL'], $_ENV['CONTACT_EMAIL'], $_SERVER['CONTACT_PHONE'], $_ENV['CONTACT_PHONE']);
        }
    }

    public function testAdminLinkIsOnlyVisibleToAdministrators(): void
    {
        $this->client->request('GET', '/');
        $this->assertStringNotContainsString('Administration', (string) $this->client->getResponse()->getContent());
        $this->assertStringNotContainsString('Espace admin', (string) $this->client->getResponse()->getContent());

        $admin = $this->em->getRepository(User::class)->findOneBy(['email' => 'admin@studio-photo.local']);
        $this->assertNotNull($admin);
        $this->client->loginUser($admin);

        $this->client->request('GET', '/');
        $this->assertStringContainsString('Administration', (string) $this->client->getResponse()->getContent());
    }
}
