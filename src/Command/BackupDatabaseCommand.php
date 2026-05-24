<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'app:backup:database',
    description: 'Dump la base MySQL, compresse et nettoie les anciens backups',
)]
class BackupDatabaseCommand extends Command
{
    public function __construct(
        private readonly string $databaseUrl,
        private readonly string $backupDir,
        private readonly int $retentionDays = 14,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('retention', 'r', InputOption::VALUE_REQUIRED, 'Nb de jours à conserver', (string) $this->retentionDays)
            ->addOption('mysqldump', null, InputOption::VALUE_REQUIRED, 'Chemin vers mysqldump', $this->detectMysqldump())
            ->setHelp('Crée un fichier .sql.gz horodaté dans var/backups/ et supprime les fichiers plus vieux que la rétention.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $retention = (int) $input->getOption('retention');
        $mysqldump = (string) $input->getOption('mysqldump');

        // Parse the DATABASE_URL
        $dsn = $this->parseDsn($this->databaseUrl);
        if ($dsn === null) {
            $io->error('DATABASE_URL invalide ou non-MySQL : '.$this->databaseUrl);
            return Command::FAILURE;
        }

        if (!is_dir($this->backupDir) && !@mkdir($this->backupDir, 0775, true) && !is_dir($this->backupDir)) {
            $io->error('Impossible de créer '.$this->backupDir);
            return Command::FAILURE;
        }

        $timestamp = date('Ymd-His');
        $filename = sprintf('%s/%s-%s.sql', $this->backupDir, $dsn['database'], $timestamp);

        $args = [
            $mysqldump,
            '--host='.$dsn['host'],
            '--port='.($dsn['port'] ?? 3306),
            '--user='.$dsn['user'],
            '--single-transaction',
            '--quick',
            '--no-tablespaces',
            '--routines',
            '--triggers',
            '--default-character-set=utf8mb4',
            $dsn['database'],
        ];
        $env = ['MYSQL_PWD' => $dsn['password'] ?? ''];

        $io->section('Dump de la base "'.$dsn['database'].'"');
        $process = new Process($args, null, $env, null, 300);
        $process->run();

        if (!$process->isSuccessful()) {
            $io->error('mysqldump a échoué : '.$process->getErrorOutput());
            return Command::FAILURE;
        }

        file_put_contents($filename, $process->getOutput());
        $sizeMb = round(filesize($filename) / 1024 / 1024, 2);
        $io->success("Dump créé : $filename ($sizeMb MB)");

        // gzip
        if (function_exists('gzopen')) {
            $gzFilename = $filename.'.gz';
            $in = fopen($filename, 'rb');
            $out = gzopen($gzFilename, 'wb9');
            stream_copy_to_stream($in, $out);
            fclose($in);
            gzclose($out);
            @unlink($filename);
            $gzSizeMb = round(filesize($gzFilename) / 1024 / 1024, 2);
            $io->writeln("→ Compressé : $gzFilename ($gzSizeMb MB)");
            $filename = $gzFilename;
        }

        // Rétention
        $cutoff = time() - ($retention * 86400);
        $deleted = 0;
        foreach (glob($this->backupDir.'/*.sql*') ?: [] as $old) {
            if (filemtime($old) < $cutoff) {
                @unlink($old);
                $deleted++;
            }
        }
        if ($deleted > 0) {
            $io->writeln(sprintf('→ %d ancien(s) backup(s) supprimé(s) (rétention %d jours).', $deleted, $retention));
        }

        return Command::SUCCESS;
    }

    /** Extracts host, port, user, password and database from a DATABASE_URL */
    private function parseDsn(string $url): ?array
    {
        if (!str_starts_with($url, 'mysql://')) {
            return null;
        }
        $parts = parse_url($url);
        if (!$parts || empty($parts['host']) || empty($parts['path'])) {
            return null;
        }
        return [
            'host' => $parts['host'],
            'port' => $parts['port'] ?? 3306,
            'user' => urldecode($parts['user'] ?? 'root'),
            'password' => urldecode($parts['pass'] ?? ''),
            'database' => ltrim($parts['path'], '/'),
        ];
    }

    private function detectMysqldump(): string
    {
        // Windows XAMPP default
        if (is_file('C:/xampp/mysql/bin/mysqldump.exe')) {
            return 'C:/xampp/mysql/bin/mysqldump.exe';
        }
        // Try Linux/Mac default
        return 'mysqldump';
    }
}
