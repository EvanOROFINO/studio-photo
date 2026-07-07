<?php

namespace App\Tests\Functional;

use App\Entity\Site;
use App\Repository\SiteRepository;
use App\Repository\VideoPackageRepository;
use App\Repository\VideoRepository;
use App\Service\SiteContext;
use App\Tests\AbstractAppWebTestCase;

class MultiSiteTest extends AbstractAppWebTestCase
{
    private function sites(): SiteRepository
    {
        return static::getContainer()->get(SiteRepository::class);
    }

    // -- Fixtures & entities --------------------------------------------

    public function testTwoActiveSitesExist(): void
    {
        $sites = $this->sites()->findAllActive();
        $this->assertCount(2, $sites, 'Fixtures should create Photo + Vidéo sites');
    }

    public function testPhotoIsDefaultSite(): void
    {
        $default = $this->sites()->findDefault();
        $this->assertNotNull($default);
        $this->assertTrue($default->isPhotoSite());
        $this->assertSame(Site::SLUG_PHOTO, $default->getSlug());
    }

    public function testVideoSiteHasDistinctAccentColor(): void
    {
        $video = $this->sites()->findBySlug(Site::SLUG_VIDEO);
        $this->assertNotNull($video);
        $this->assertSame('#a78bfa', $video->getAccentColor());
        $this->assertSame('🎬', $video->getIconEmoji());
    }

    // -- Site detection & context ---------------------------------------

    public function testDefaultRequestResolvesToPhotoSite(): void
    {
        $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();

        $context = static::getContainer()->get(SiteContext::class);
        $this->assertTrue($context->isPhoto());
    }

    public function testSiteOverrideSwitchesToVideo(): void
    {
        $crawler = $this->client->request('GET', '/?_site=video');
        $this->assertResponseIsSuccessful();
        // La home vidéo affiche son accroche cinématographique
        $this->assertStringContainsString(
            'Raconter votre histoire',
            $this->client->getResponse()->getContent(),
        );
    }

    // -- Video-specific routes ------------------------------------------

    public function testVideoHomeListsCategories(): void
    {
        $this->client->request('GET', '/?_site=video');
        $content = $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Mariage', $content);
        $this->assertStringContainsString('Corporate', $content);
    }

    public function testShowreelFiltersByCategory(): void
    {
        $this->client->request('GET', '/showreel?category=mariage&_site=video');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Mariage', $this->client->getResponse()->getContent());
    }

    public function testVideoPackagesPageShowsThreeOffers(): void
    {
        $this->client->request('GET', '/forfaits-video?_site=video');
        $this->assertResponseIsSuccessful();

        $content = $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Essentiel', $content);
        $this->assertStringContainsString('Signature', $content);
        $this->assertStringContainsString('Prestige', $content);
    }

    public function testVideoDetailPageRenders(): void
    {
        $videoRepo = static::getContainer()->get(VideoRepository::class);
        $siteVideo = $this->sites()->findBySlug(Site::SLUG_VIDEO);
        $videos = $videoRepo->findForSite($siteVideo);
        $this->assertNotEmpty($videos, 'Video site should have demo videos');

        $this->client->request('GET', '/showreel/'.$videos[0]->getId().'?_site=video');
        $this->assertResponseIsSuccessful();
    }

    // -- Contact linked to packages -------------------------------------

    public function testContactPrefillsSelectedPackage(): void
    {
        $this->client->request('GET', '/contact?forfait=signature&_site=video');
        $this->assertResponseIsSuccessful();

        $content = $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Signature', $content);
        $this->assertStringContainsString('sélectionné', $content);
    }

    // -- Site switcher ---------------------------------------------------

    public function testSiteSwitchRedirects(): void
    {
        $this->client->request('GET', '/changer-de-site/video');
        $this->assertResponseRedirects();
    }

    // -- Data isolation --------------------------------------------------

    public function testVideoPackagesBelongToVideoSiteOnly(): void
    {
        $repo = static::getContainer()->get(VideoPackageRepository::class);
        $siteVideo = $this->sites()->findBySlug(Site::SLUG_VIDEO);
        $sitePhoto = $this->sites()->findBySlug(Site::SLUG_PHOTO);

        $this->assertCount(3, $repo->findActiveForSite($siteVideo));
        $this->assertCount(0, $repo->findActiveForSite($sitePhoto));
    }
}
