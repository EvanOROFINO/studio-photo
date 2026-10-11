<?php

namespace App\Tests\Functional;

use App\Entity\Site;
use App\Entity\User;
use App\Entity\VideoCategory;
use App\Tests\AbstractAppWebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Commandes utilisées à la mise en ligne : création de l'administrateur et
 * installation des données de base sans jamais écraser le travail du photographe.
 */
class DeploymentCommandsTest extends AbstractAppWebTestCase
{
    private const STRONG_PASSWORD = 'Un-vrai-mot-de-passe-2026';

    protected function tearDown(): void
    {
        foreach (['ADMIN_EMAIL', 'ADMIN_PASSWORD'] as $name) {
            unset($_SERVER[$name], $_ENV[$name]);
            putenv($name);
        }
        parent::tearDown();
    }

    /**
     * @param array<string, mixed>  $input
     * @param array<string, string> $env
     */
    private function runCommand(string $name, array $input = [], array $env = []): CommandTester
    {
        foreach ($env as $key => $value) {
            $_SERVER[$key] = $value;
            putenv($key.'='.$value);
        }

        $application = new Application(static::$kernel);
        $tester = new CommandTester($application->find($name));
        $tester->execute($input, ['interactive' => false]);

        return $tester;
    }

    private function findUser(string $email): ?User
    {
        $this->em->clear();

        return $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    private function passwordHasher(): UserPasswordHasherInterface
    {
        return static::getContainer()->get(UserPasswordHasherInterface::class);
    }

    // -- app:create-admin ------------------------------------------------

    public function testCreateAdminCreatesAnAdminWithAHashedPassword(): void
    {
        $tester = $this->runCommand(
            'app:create-admin',
            ['--email' => 'Nouveau@Studio.fr', '--name' => 'Nouvel admin'],
            ['ADMIN_PASSWORD' => self::STRONG_PASSWORD],
        );

        $this->assertSame(0, $tester->getStatusCode());
        $user = $this->findUser('nouveau@studio.fr');
        $this->assertNotNull($user, 'Le compte doit être créé, avec l\'e-mail en minuscules.');
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertSame('Nouvel admin', $user->getFullName());
        $this->assertNotSame(self::STRONG_PASSWORD, $user->getPassword(), 'Le mot de passe ne doit jamais être stocké en clair.');
        $this->assertTrue($this->passwordHasher()->isPasswordValid($user, self::STRONG_PASSWORD));
        $this->assertStringNotContainsString(self::STRONG_PASSWORD, $tester->getDisplay());
    }

    public function testCreateAdminReadsTheEmailFromTheEnvironment(): void
    {
        $tester = $this->runCommand('app:create-admin', [], [
            'ADMIN_EMAIL' => 'depuis-env@studio.fr',
            'ADMIN_PASSWORD' => self::STRONG_PASSWORD,
        ]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertNotNull($this->findUser('depuis-env@studio.fr'));
    }

    public function testCreateAdminRejectsATooShortPassword(): void
    {
        $tester = $this->runCommand('app:create-admin', ['--email' => 'court@studio.fr'], ['ADMIN_PASSWORD' => 'admin']);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('trop court', $tester->getDisplay());
        $this->assertNull($this->findUser('court@studio.fr'));
    }

    public function testCreateAdminFailsWithoutAPasswordWhenNotInteractive(): void
    {
        $tester = $this->runCommand('app:create-admin', ['--email' => 'sans-mdp@studio.fr']);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('ADMIN_PASSWORD', $tester->getDisplay());
        $this->assertNull($this->findUser('sans-mdp@studio.fr'));
    }

    public function testCreateAdminRefusesAnExistingAccountWithoutUpdate(): void
    {
        $tester = $this->runCommand(
            'app:create-admin',
            ['--email' => 'admin@studio-photo.local'],
            ['ADMIN_PASSWORD' => self::STRONG_PASSWORD],
        );

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('existe déjà', $tester->getDisplay());

        // Le mot de passe de démonstration n'a pas bougé
        $admin = $this->findUser('admin@studio-photo.local');
        $this->assertTrue($this->passwordHasher()->isPasswordValid($admin, 'admin'));
    }

    public function testCreateAdminUpdateChangesThePassword(): void
    {
        $tester = $this->runCommand(
            'app:create-admin',
            ['--email' => 'admin@studio-photo.local', '--update' => true],
            ['ADMIN_PASSWORD' => self::STRONG_PASSWORD],
        );

        $this->assertSame(0, $tester->getStatusCode());
        $admin = $this->findUser('admin@studio-photo.local');
        $this->assertTrue($this->passwordHasher()->isPasswordValid($admin, self::STRONG_PASSWORD));
        $this->assertFalse($this->passwordHasher()->isPasswordValid($admin, 'admin'), 'L\'ancien mot de passe doit être refusé.');
    }

    public function testCreateAdminIfNoneDoesNothingWhenAnAccountExists(): void
    {
        $tester = $this->runCommand(
            'app:create-admin',
            ['--email' => 'autre@studio.fr', '--if-none' => true],
            ['ADMIN_PASSWORD' => self::STRONG_PASSWORD],
        );

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertNull($this->findUser('autre@studio.fr'));
    }

    // -- app:install:sites / app:install:video-categories -----------------

    public function testInstallSitesOnlyMissingNeverOverwritesExistingSites(): void
    {
        $photo = $this->em->getRepository(Site::class)->findOneBy(['slug' => Site::SLUG_PHOTO]);
        $photo->setName('Le nom choisi par le photographe');
        $photo->setDomain('mon-studio.fr');
        $this->em->flush();

        $tester = $this->runCommand('app:install:sites', ['--only-missing' => true]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->em->clear();
        $photo = $this->em->getRepository(Site::class)->findOneBy(['slug' => Site::SLUG_PHOTO]);
        $this->assertSame('Le nom choisi par le photographe', $photo->getName());
        $this->assertSame('mon-studio.fr', $photo->getDomain());
    }

    public function testInstallSitesAppliesTheRequestedDomainsWhenUpdating(): void
    {
        $this->runCommand('app:install:sites', [
            '--photo-domain' => 'photo.exemple.org',
            '--video-domain' => 'video.exemple.org',
        ]);

        $this->em->clear();
        $sites = $this->em->getRepository(Site::class);
        $this->assertSame('photo.exemple.org', $sites->findOneBy(['slug' => Site::SLUG_PHOTO])->getDomain());
        $this->assertSame('video.exemple.org', $sites->findOneBy(['slug' => Site::SLUG_VIDEO])->getDomain());
    }

    public function testInstallVideoCategoriesOnlyMissingKeepsCustomisation(): void
    {
        $category = $this->em->getRepository(VideoCategory::class)->findOneBy(['slug' => 'mariage']);
        $this->assertNotNull($category, 'Les fixtures créent la catégorie « mariage ».');
        $category->setName('Mes films de mariage');
        $this->em->flush();

        $this->runCommand('app:install:video-categories', ['--only-missing' => true]);
        $this->em->clear();
        $category = $this->em->getRepository(VideoCategory::class)->findOneBy(['slug' => 'mariage']);
        $this->assertSame('Mes films de mariage', $category->getName());

        // Sans --only-missing, la commande remet les valeurs par défaut (comportement historique)
        $this->runCommand('app:install:video-categories');
        $this->em->clear();
        $category = $this->em->getRepository(VideoCategory::class)->findOneBy(['slug' => 'mariage']);
        $this->assertSame('Mariage', $category->getName());
    }
}
