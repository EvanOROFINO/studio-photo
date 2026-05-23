<?php

namespace App\Tests\Functional;

use App\Tests\AbstractAppWebTestCase;

class PublicRoutesSmokeTest extends AbstractAppWebTestCase
{
    /** @return iterable<array{string, string}> */
    public static function publicUrlsProvider(): iterable
    {
        yield 'home' => ['/', 'Capturer'];
        yield 'gallery' => ['/galerie', 'Galerie'];
        yield 'services' => ['/prestations', 'Prestations'];
        yield 'about' => ['/a-propos', 'À propos'];
        yield 'testimonials' => ['/temoignages', 'm\'ont fait confiance'];
        yield 'contact' => ['/contact', 'Discutons'];
        yield 'faq' => ['/faq', 'questions'];
        yield 'availability' => ['/disponibilites', 'Disponibilités'];
        yield 'blog' => ['/blog', 'Articles'];
        yield 'feed' => ['/feed', '<rss'];
        yield 'sitemap' => ['/sitemap', '<urlset'];
        yield 'legal_notice' => ['/mentions-legales', 'Mentions légales'];
        yield 'privacy' => ['/politique-de-confidentialite', 'confidentialité'];
        yield 'login' => ['/login', 'Espace administrateur'];
    }

    /** @dataProvider publicUrlsProvider */
    public function testPublicUrlIsReachable(string $url, string $expectedContent): void
    {
        $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful(sprintf('GET %s should return 2xx', $url));

        $content = $this->client->getResponse()->getContent();
        $this->assertStringContainsString(
            $expectedContent,
            $content,
            sprintf('Page %s should contain "%s"', $url, $expectedContent),
        );
    }

    public function testAdminRequiresAuthentication(): void
    {
        $this->client->request('GET', '/admin');
        $this->assertResponseRedirects();
    }
}
