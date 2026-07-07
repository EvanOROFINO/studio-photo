<?php

namespace App\Command;

use App\Entity\VideoCategory;
use App\Repository\VideoCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:install:video-categories',
    description: 'Crée les catégories vidéo de base (Mariage, Corporate, Clip)',
)]
class InstallVideoCategoriesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly VideoCategoryRepository $repository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $categories = [
            ['slug' => 'mariage', 'name' => 'Mariage', 'iconEmoji' => '💍', 'position' => 1, 'description' => 'Films de mariage cinématographiques, racontés à votre rythme.'],
            ['slug' => 'corporate', 'name' => 'Corporate', 'iconEmoji' => '🏢', 'position' => 2, 'description' => 'Films institutionnels, témoignages collaborateurs, événements pro.'],
            ['slug' => 'clip', 'name' => 'Clip & Brand', 'iconEmoji' => '🎵', 'position' => 3, 'description' => 'Clips musicaux, films de marque, contenus publicitaires.'],
            ['slug' => 'evenement', 'name' => 'Événement', 'iconEmoji' => '🎉', 'position' => 4, 'description' => 'Aftermovies, soirées, conférences, lancements de produit.'],
        ];

        $created = 0;
        $updated = 0;

        foreach ($categories as $data) {
            $cat = $this->repository->findOneBy(['slug' => $data['slug']]);
            if ($cat === null) {
                $cat = new VideoCategory();
                $created++;
            } else {
                $updated++;
            }
            $cat->setSlug($data['slug']);
            $cat->setName($data['name']);
            $cat->setIconEmoji($data['iconEmoji']);
            $cat->setDescription($data['description']);
            $cat->setPosition($data['position']);
            $cat->setIsActive(true);

            $this->em->persist($cat);
            $io->text(sprintf('  %s %s', $cat->getIconEmoji(), $cat->getName()));
        }

        $this->em->flush();
        $io->success(sprintf('%d catégorie(s) créée(s), %d mise(s) à jour.', $created, $updated));

        return Command::SUCCESS;
    }
}
