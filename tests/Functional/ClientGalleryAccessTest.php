<?php

namespace App\Tests\Functional;

use App\Entity\ClientFilm;
use App\Entity\ClientGallery;
use App\Tests\AbstractAppWebTestCase;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class ClientGalleryAccessTest extends AbstractAppWebTestCase
{
    private function createGallery(string $plainPassword = 'testpass'): ClientGallery
    {
        $hasher = static::getContainer()->get(PasswordHasherFactoryInterface::class)->getPasswordHasher('common');

        $gallery = new ClientGallery();
        $gallery->setTitle('Test Gallery');
        $gallery->setClientName('Test Client');
        $gallery->setClientEmail('client@example.fr');
        $gallery->setPasswordHash($hasher->hash($plainPassword));
        $gallery->setActive(true);

        $this->em->persist($gallery);
        $this->em->flush();

        return $gallery;
    }

    public function testInvalidTokenReturns404(): void
    {
        $this->client->request('GET', '/galerie-client/00000000000000000000000000000000');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testLoginPageRendersWithGalleryTitle(): void
    {
        $gallery = $this->createGallery();
        $this->client->request('GET', '/galerie-client/'.$gallery->getToken());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Test Gallery');
    }

    public function testWrongPasswordKeepsUserOnLoginPage(): void
    {
        $gallery = $this->createGallery('correctpass');
        $crawler = $this->client->request('GET', '/galerie-client/'.$gallery->getToken());

        $token = $crawler->filter('input[name="_token"]')->attr('value');
        $this->client->request('POST', '/galerie-client/'.$gallery->getToken(), [
            'password' => 'wrongpass',
            '_token' => $token,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert', 'Mot de passe incorrect');
    }

    public function testCorrectPasswordRedirectsToPhotosPage(): void
    {
        $gallery = $this->createGallery('rightpass');
        $crawler = $this->client->request('GET', '/galerie-client/'.$gallery->getToken());

        $token = $crawler->filter('input[name="_token"]')->attr('value');
        $this->client->request('POST', '/galerie-client/'.$gallery->getToken(), [
            'password' => 'rightpass',
            '_token' => $token,
        ]);

        $this->assertResponseRedirects('/galerie-client/'.$gallery->getToken().'/photos');
    }

    public function testUnauthAccessToPhotosRedirectsToGalleryLogin(): void
    {
        $gallery = $this->createGallery();
        $this->client->request('GET', '/galerie-client/'.$gallery->getToken().'/photos');
        $this->assertResponseRedirects('/galerie-client/'.$gallery->getToken());
    }

    public function testDeliveredFilmAppearsInUnlockedGallery(): void
    {
        $gallery = $this->createGallery('filmpass');

        $film = new ClientFilm();
        $film->setTitle('Votre film de mariage');
        $film->setUrl('https://vimeo.com/76979871');
        $film->setDuration('6:24');
        $gallery->addFilm($film);
        $this->em->persist($film);
        $this->em->flush();

        // Le parsing d'URL doit détecter Vimeo + l'ID
        $this->assertSame(ClientFilm::SOURCE_VIMEO, $film->getSource());
        $this->assertSame('76979871', $film->getExternalId());
        $this->assertStringContainsString('player.vimeo.com/video/76979871', $film->getEmbedUrl());

        // Déverrouillage puis affichage du film dans la galerie
        $crawler = $this->client->request('GET', '/galerie-client/'.$gallery->getToken());
        $token = $crawler->filter('input[name="_token"]')->attr('value');
        $this->client->request('POST', '/galerie-client/'.$gallery->getToken(), [
            'password' => 'filmpass',
            '_token' => $token,
        ]);
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $content = $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Votre film de mariage', $content);
        $this->assertStringContainsString('player.vimeo.com/video/76979871', $content);
    }
}
