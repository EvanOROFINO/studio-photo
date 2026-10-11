<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Tests\AbstractAppWebTestCase;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

/**
 * Parcourt l'admin EasyAdmin en tant qu'administrateur connecté : tableau de bord puis,
 * pour chaque section (CRUD), la liste, la création, les filtres, la fiche et l'édition.
 *
 * Garde-fou : une mise à jour d'EasyAdmin qui casse le menu ou une section est détectée
 * ici (c'était le cas avec la 5.1, qui a supprimé MenuItem::linkToCrud()).
 *
 * EasyAdmin exige l'extension PHP intl pour ses champs date/heure : sans elle le test est
 * ignoré (en local : `php -d extension=intl vendor/bin/phpunit`).
 */
#[RequiresPhpExtension('intl')]
class AdminPagesTest extends AbstractAppWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $admin = $this->em->getRepository(User::class)->findOneBy(['email' => 'admin@studio-photo.local']);
        $this->assertNotNull($admin, 'Les fixtures doivent créer le compte admin.');
        $this->client->loginUser($admin);
    }

    public function testDashboardRendersWithMenuToMainSections(): void
    {
        $this->client->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
        $html = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Bienvenue', $html);

        foreach (['/admin/photo', '/admin/video', '/admin/client-gallery', '/admin/booking'] as $link) {
            $this->assertStringContainsString($link, $html, sprintf('Le menu admin doit pointer vers %s.', $link));
        }
    }

    public function testEveryAdminSectionRenders(): void
    {
        $router = static::getContainer()->get('router');
        $failures = [];
        $sections = 0;
        $pages = 0;

        foreach ($router->getRouteCollection() as $name => $route) {
            if (!str_starts_with($name, 'admin_') || !str_ends_with($name, '_index')) {
                continue;
            }

            $crudFqcn = $route->getDefault('crudControllerFqcn');
            if (!is_string($crudFqcn) || !is_subclass_of($crudFqcn, AbstractCrudController::class)) {
                continue;
            }

            ++$sections;
            $base = $route->getPath(); // ex. /admin/photo
            $entity = $this->em->getRepository($crudFqcn::getEntityFqcn())->findOneBy([]);

            $urls = [$base, $base.'/new', $base.'/render-filters'];
            if ($entity !== null) {
                $urls[] = $base.'/'.$entity->getId();
                $urls[] = $base.'/'.$entity->getId().'/edit';
            }

            foreach ($urls as $url) {
                $this->client->request('GET', $url);
                $status = $this->client->getResponse()->getStatusCode();
                ++$pages;

                // 403 = action volontairement désactivée dans ce CRUD (ex. création d'une réservation)
                if (!in_array($status, [200, 403], true)) {
                    $failures[] = sprintf('%s -> HTTP %d %s', $url, $status, $this->errorSummary());
                }
            }
        }

        $this->assertGreaterThanOrEqual(20, $sections, 'Toutes les sections d\'admin doivent être découvertes.');
        $this->assertGreaterThan(60, $pages);
        $this->assertSame([], $failures, "Pages d'admin en erreur :\n".implode("\n", $failures));
    }

    private function errorSummary(): string
    {
        $content = (string) $this->client->getResponse()->getContent();
        if (preg_match('#<title>(.*?)</title>#s', $content, $m)) {
            return trim((string) preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($m[1]))));
        }

        return '';
    }
}
