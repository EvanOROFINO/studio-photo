<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un compte administrateur (ou change le mot de passe d\'un compte existant avec --update)',
)]
class CreateAdminCommand extends Command
{
    /** Longueur minimale du mot de passe : refuse d'office les « admin », « password »… */
    public const MIN_PASSWORD_LENGTH = 12;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $users,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp(<<<'HELP'
                Le mot de passe n'est volontairement PAS une option de la ligne de commande
                (il finirait dans l'historique du terminal et la liste des processus).
                Il est lu dans la variable d'environnement ADMIN_PASSWORD ou saisi (masqué).
                L'e-mail peut aussi venir de la variable ADMIN_EMAIL.

                  php bin/console app:create-admin --email vous@exemple.fr
                  php bin/console app:create-admin --email vous@exemple.fr --update   (change le mot de passe)
                  php bin/console app:create-admin --if-none   (déploiement : ne fait rien s'il y a déjà un compte)
                HELP)
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Adresse e-mail du compte (sinon variable ADMIN_EMAIL, sinon question)')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Nom affiché dans l\'admin', 'Administrateur')
            ->addOption('if-none', null, InputOption::VALUE_NONE, 'Ne rien faire si un compte existe déjà (scripts de déploiement)')
            ->addOption('update', null, InputOption::VALUE_NONE, 'Si le compte existe déjà, change son mot de passe');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('if-none') && $this->users->count([]) > 0) {
            $io->note('Un compte existe déjà : rien à faire.');

            return Command::SUCCESS;
        }

        $email = $this->resolveEmail($input, $io);
        if ($email === null || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('Adresse e-mail manquante ou invalide (option --email ou variable ADMIN_EMAIL).');

            return Command::FAILURE;
        }
        $email = mb_strtolower($email);

        $user = $this->users->findOneBy(['email' => $email]);
        if ($user !== null && !$input->getOption('update')) {
            $io->error(sprintf('Le compte %s existe déjà. Ajoutez --update pour changer son mot de passe.', $email));

            return Command::FAILURE;
        }

        $password = $this->resolvePassword($input, $io);
        if ($password === null) {
            return Command::FAILURE;
        }
        if (mb_strlen($password) < self::MIN_PASSWORD_LENGTH) {
            $io->error(sprintf('Mot de passe trop court : %d caractères minimum.', self::MIN_PASSWORD_LENGTH));

            return Command::FAILURE;
        }

        $isNew = $user === null;
        if ($isNew) {
            $user = new User();
            $user->setEmail($email);
            $user->setFullName((string) $input->getOption('name'));
            $user->setRoles(['ROLE_ADMIN']);
        }
        $user->setPassword($this->hasher->hashPassword($user, $password));

        $this->em->persist($user);
        $this->em->flush();

        $io->success(sprintf('Compte administrateur %s : %s', $isNew ? 'créé' : 'mis à jour', $email));

        return Command::SUCCESS;
    }

    private function resolveEmail(InputInterface $input, SymfonyStyle $io): ?string
    {
        $email = trim((string) ($input->getOption('email') ?? ''));
        if ($email === '') {
            $email = trim($this->env('ADMIN_EMAIL'));
        }
        if ($email === '' && $input->isInteractive()) {
            $email = trim((string) $io->ask('Adresse e-mail de l\'administrateur'));
        }

        return $email === '' ? null : $email;
    }

    private function resolvePassword(InputInterface $input, SymfonyStyle $io): ?string
    {
        $password = $this->env('ADMIN_PASSWORD');
        if ($password !== '') {
            return $password;
        }

        if (!$input->isInteractive()) {
            $io->error('Mot de passe manquant : définissez la variable d\'environnement ADMIN_PASSWORD.');

            return null;
        }

        $question = new Question(sprintf('Mot de passe (%d caractères minimum)', self::MIN_PASSWORD_LENGTH));
        $question->setHidden(true);
        $first = (string) $io->askQuestion($question);

        $confirm = new Question('Confirmez le mot de passe');
        $confirm->setHidden(true);
        if ($first !== (string) $io->askQuestion($confirm)) {
            $io->error('Les deux mots de passe sont différents.');

            return null;
        }

        return $first;
    }

    private function env(string $name): string
    {
        $value = $_SERVER[$name] ?? $_ENV[$name] ?? getenv($name);

        return is_string($value) ? $value : '';
    }
}
