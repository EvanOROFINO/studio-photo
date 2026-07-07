<?php

namespace App\Command;

use App\Entity\Site;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:install:sites',
    description: 'Crée (ou met à jour) les 2 sites par défaut : Studio Photo et Studio Vidéo',
)]
class InstallSitesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SiteRepository $siteRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $sites = [
            [
                'slug' => Site::SLUG_PHOTO,
                'name' => 'Studio Photo',
                'domain' => 'studio-photo.fr',
                'domainStaging' => '127.0.0.1:8000',
                'tagline' => 'Capturer l\'instant. Sublimer l\'émotion.',
                'primaryColor' => '#1a1a1a',
                'accentColor' => '#c8a97e',
                'iconEmoji' => '📸',
                'position' => 1,
                'isDefault' => true,
            ],
            [
                'slug' => Site::SLUG_VIDEO,
                'name' => 'Studio Vidéo',
                'domain' => 'studio-video.fr',
                'domainStaging' => 'video.localhost:8000',
                'tagline' => 'Raconter une histoire. Animer une marque.',
                'primaryColor' => '#1a1a1a',
                'accentColor' => '#a78bfa',
                'iconEmoji' => '🎬',
                'position' => 2,
                'isDefault' => false,
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($sites as $data) {
            $site = $this->siteRepository->findBySlug($data['slug']);

            if ($site === null) {
                $site = new Site();
                $created++;
            } else {
                $updated++;
            }

            $site->setSlug($data['slug']);
            $site->setName($data['name']);
            $site->setDomain($data['domain']);
            $site->setDomainStaging($data['domainStaging']);
            $site->setTagline($data['tagline']);
            $site->setPrimaryColor($data['primaryColor']);
            $site->setAccentColor($data['accentColor']);
            $site->setIconEmoji($data['iconEmoji']);
            $site->setPosition($data['position']);
            $site->setIsDefault($data['isDefault']);
            $site->setIsActive(true);

            $this->em->persist($site);
            $io->text(sprintf('  %s %s (%s)', $site->getIconEmoji(), $site->getName(), $site->getDomain()));
        }

        $this->em->flush();

        $io->success(sprintf('%d site(s) créé(s), %d mis à jour. Total actif : %d.',
            $created,
            $updated,
            count($this->siteRepository->findAllActive())
        ));

        return Command::SUCCESS;
    }
}
