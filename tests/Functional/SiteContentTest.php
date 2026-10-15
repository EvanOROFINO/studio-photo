<?php

namespace App\Tests\Functional;

use App\Entity\Category;
use App\Entity\Photo;
use App\Tests\AbstractAppWebTestCase;

/**
 * Règles de contenu demandées par le photographe (notes « Studio Photo / Studio Vidéo ») :
 * mêmes thèmes partout, bandeau de portfolio commun, bascule Photos/Vidéo, mentions « À propos »,
 * accroches du Studio Vidéo, photos rangées dans leur thème.
 */
class SiteContentTest extends AbstractAppWebTestCase
{
    private const PHOTO_THEMES = ['Mariages', 'Portraits', 'Événements', 'Grossesse & Bébé'];

    /** Contenu de la page en texte lisible (entités HTML décodées, espaces normalisés). */
    private function text(): string
    {
        $content = (string) $this->client->getResponse()->getContent();

        return (string) preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5));
    }

    // -- 1. Cohérence photos / thèmes ------------------------------------------

    public function testEveryPhotoIsFiledUnderItsOwnTheme(): void
    {
        $expected = [
            'Mariages' => ['Cérémonie traditionnelle', 'Larmes de joie', 'Le voile et le sourire', 'L\'échange des alliances', 'Le bouquet de la mariée', 'Première danse'],
            'Portraits' => ['Portrait en lumière naturelle', 'Sourire naturel', 'Portrait artistique'],
            'Événements' => ['Conférence tech', 'Soirée de lancement', 'Prise de parole'],
            'Grossesse & Bébé' => ['Attendre en douceur', 'Maternité en lumière douce', 'Premiers jours', 'Petits petons'],
        ];

        $repository = $this->em->getRepository(Category::class);
        foreach ($expected as $themeName => $titles) {
            $category = $repository->findOneBy(['name' => $themeName]);
            $this->assertNotNull($category, sprintf('Le thème « %s » doit exister.', $themeName));

            $actual = [];
            foreach ($category->getPhotos() as $photo) {
                $actual[] = $photo->getTitle();
            }
            sort($actual);
            $wanted = $titles;
            sort($wanted);
            $this->assertSame($wanted, $actual, sprintf('Le thème « %s » ne doit contenir que ses propres photos.', $themeName));
        }
    }

    public function testPagesRenderWhenPhotosHaveAnUploadedImage(): void
    {
        // Les fixtures de test n'ont pas d'image ; avec une vraie photo envoyée, les pages passent par
        // |imagine_filter, qui exige la route de miniatures (liip_imagine_filter) : sans elle → erreur 500.
        foreach ($this->em->getRepository(Photo::class)->findAll() as $i => $photo) {
            $photo->setImageName('demo-test-'.$i.'.jpg');
        }
        $this->em->flush();

        $mariages = $this->em->getRepository(Category::class)->findOneBy(['name' => 'Mariages']);
        foreach (['/', '/galerie', '/galerie/'.$mariages->getSlug()] as $url) {
            $this->client->request('GET', $url);
            $this->assertResponseIsSuccessful(sprintf('%s doit s\'afficher avec des photos envoyées.', $url));
            $this->assertStringContainsString('/media/cache/', (string) $this->client->getResponse()->getContent());
        }
    }

    // -- 2/3. Mêmes thèmes sur Accueil, Galerie, portfolios -----------------------

    public function testHomeGalleryPrestationsAndContactShowTheSameFourThemes(): void
    {
        // Notes du photographe : « Accueil / Galerie / Prestations → les mêmes thématiques »
        foreach (['/', '/galerie', '/prestations', '/contact'] as $url) {
            $this->client->request('GET', $url);
            $this->assertResponseIsSuccessful();
            $text = $this->text();
            foreach (self::PHOTO_THEMES as $theme) {
                $this->assertStringContainsString($theme, $text, sprintf('« %s » doit apparaître sur %s.', $theme, $url));
            }
        }
    }

    public function testExploreByThemeIsNotLimitedToFourThemes(): void
    {
        $extra = new Category();
        $extra->setName('Famille');
        $extra->setPosition(5);
        $this->em->persist($extra);
        $this->em->flush();

        $this->client->request('GET', '/');
        $this->assertStringContainsString('Famille', $this->text(), 'Un 5e thème doit apparaître dans « Explorer par thème ».');

        $this->client->request('GET', '/galerie');
        $this->assertStringContainsString('Famille', $this->text(), 'Et dans les pastilles de la galerie.');
    }

    public function testVideoCategoriesAreTheSameOnHomeShowreelAndPackages(): void
    {
        foreach (['/?_site=video', '/showreel?_site=video', '/forfaits-video?_site=video'] as $url) {
            $this->client->request('GET', $url);
            $this->assertResponseIsSuccessful();
            $text = $this->text();
            foreach (['Mariage', 'Corporate', 'Clip & Brand', 'Événement'] as $category) {
                $this->assertStringContainsString($category, $text, sprintf('« %s » doit apparaître sur %s.', $category, $url));
            }
        }
    }

    // -- 8. Bandeau commun des portfolios -----------------------------------------

    public function testEveryShowcasePageUsesTheSameBannerComponent(): void
    {
        $mariages = $this->em->getRepository(Category::class)->findOneBy(['name' => 'Mariages']);

        $expected = [
            '/galerie' => 'Galerie',
            '/galerie/'.$mariages->getSlug() => 'Mariages', // le thème porte le même titre que partout
            '/prestations' => 'Prestations',
            '/showreel?_site=video' => 'Showreel',
            '/forfaits-video?_site=video' => 'Forfaits',
        ];

        foreach ($expected as $url => $title) {
            $this->client->request('GET', $url);
            $this->assertResponseIsSuccessful();
            $this->assertSelectorTextSame('header.page-banner h1', $title, sprintf('Le bandeau commun de %s doit porter le titre « %s ».', $url, $title));
        }
    }

    public function testPrestationsAreExactlyTheFourThemesLinkedToTheGallery(): void
    {
        $this->client->request('GET', '/prestations');
        $this->assertResponseIsSuccessful();
        $text = $this->text();

        foreach (self::PHOTO_THEMES as $theme) {
            $this->assertStringContainsString($theme, $text);
            $category = $this->em->getRepository(Category::class)->findOneBy(['name' => $theme]);
            $this->assertSelectorExists(sprintf('a[href="/galerie/%s"]', $category->getSlug()), sprintf('La prestation « %s » doit renvoyer vers sa galerie.', $theme));
        }

        // Plus de formules hors thèmes
        $this->assertStringNotContainsString('Séance famille', $text);
        $this->assertStringNotContainsString('Shooting produit', $text);
    }

    // -- 4. Bascule Photos / Vidéo -------------------------------------------------

    public function testSiteSwitchShowsPhotosAboveVideo(): void
    {
        $this->client->request('GET', '/');
        $html = (string) $this->client->getResponse()->getContent();

        $start = strpos($html, 'class="site-switch"');
        $this->assertNotFalse($start, 'La bascule Photos / Vidéo doit être présente.');
        $switch = substr($html, $start, 1200);

        $photos = strpos($switch, 'Photos');
        $video = strpos($switch, 'Vidéo');
        $this->assertNotFalse($photos);
        $this->assertNotFalse($video);
        $this->assertLessThan($video, $photos, '« Photos » doit être placé avant (au-dessus de) « Vidéo ».');
    }

    // -- 5. Page « À propos » -------------------------------------------------------

    public function testAboutPageStatesTheTradeOfEachSite(): void
    {
        $this->client->request('GET', '/a-propos?_site=photo');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Je suis votre photographe et pas vidéaste', $this->text());

        $this->client->request('GET', '/a-propos?_site=video');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Je suis votre vidéaste et pas photographe', $this->text());
    }

    // -- 6. Textes du Studio Vidéo ---------------------------------------------------

    public function testVideoHomeShowsTheStudioPitch(): void
    {
        $this->client->request('GET', '/?_site=video');
        $this->assertResponseIsSuccessful();
        $text = $this->text();

        // Les accroches sont tout en haut (dans l'en-tête, avant la section « Mes films »)
        $this->assertLessThan(
            mb_strpos($text, 'Mes films'),
            mb_strpos($text, 'Modernes, polyvalentes et professionnelles'),
            'Le titre doit apparaître avant le showreel, dès l\'arrivée sur le site.',
        );
        $this->assertLessThan(
            mb_strpos($text, 'Mes films'),
            mb_strpos($text, 'Du jour J aux grands événements'),
        );

        $this->assertStringContainsString('Modernes, polyvalentes et professionnelles', $text);
        $this->assertStringContainsString('Mariages, événements et projets sur mesure', $text);
        $this->assertStringContainsString('Le film unique de tous vos moments d\'exception', $text);
        $this->assertStringContainsString('Capturer l\'émotion, sublimer l\'instant', $text);
        $this->assertStringContainsString('Une présence vidéo unique pour vos événements privés et professionnels', $text);
        $this->assertStringContainsString('Du jour J aux grands événements', $text);
        $this->assertStringContainsString('La vidéo qui donne du sens et de l\'impact à vos projets', $text);
    }
}
