<?php

namespace App\Tests\Functional;

use App\Tests\AbstractAppWebTestCase;

/**
 * La page de connexion de l'admin doit bloquer le brute-force :
 * 5 tentatives échouées, puis refus temporaire — même avec le bon mot de passe.
 */
class LoginThrottlingTest extends AbstractAppWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->clearAttemptCounters();
    }

    protected function tearDown(): void
    {
        $this->clearAttemptCounters();
        parent::tearDown();
    }

    /** Le compteur de tentatives est stocké dans un cache qui survit entre deux exécutions. */
    private function clearAttemptCounters(): void
    {
        static::getContainer()->get('cache.rate_limiter')->clear();
    }

    private function attemptLogin(string $password): string
    {
        $this->client->request(
            'POST',
            '/login',
            ['_username' => 'admin@studio-photo.local', '_password' => $password, '_csrf_token' => 'csrf-token'],
            [],
            ['HTTP_ORIGIN' => 'http://localhost'],
        );

        if ($this->client->getResponse()->isRedirect()) {
            $this->client->followRedirect();
        }

        return (string) $this->client->getResponse()->getContent();
    }

    public function testRepeatedFailedLoginsAreThrottled(): void
    {
        for ($i = 1; $i <= 5; ++$i) {
            $content = $this->attemptLogin('mauvais-mot-de-passe-'.$i);
            $this->assertDoesNotMatchRegularExpression('/trop de tentatives|too many/i', $content, sprintf('La tentative %d ne doit pas encore être bloquée.', $i));
        }

        $content = $this->attemptLogin('mauvais-mot-de-passe-6');
        $this->assertMatchesRegularExpression('/trop de tentatives|too many/i', $content, 'La 6e tentative doit être bloquée.');
    }

    public function testCorrectPasswordIsAlsoRefusedOnceThrottled(): void
    {
        for ($i = 1; $i <= 5; ++$i) {
            $this->attemptLogin('mauvais-mot-de-passe-'.$i);
        }

        // Le bon mot de passe ne contourne pas le blocage
        $this->attemptLogin('admin');
        $this->client->request('GET', '/admin');
        $this->assertResponseRedirects('/login');
    }

    public function testASingleCorrectLoginStillWorks(): void
    {
        $this->attemptLogin('admin');

        $this->client->request('GET', '/admin');
        $this->assertTrue(
            $this->client->getResponse()->isSuccessful() || $this->client->getResponse()->isRedirect(),
        );
        $this->assertNotSame('/login', $this->client->getResponse()->headers->get('Location'));
    }
}
