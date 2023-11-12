<?php declare(strict_types=1);

namespace App\Command;

use App\Importer\ExamImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Path;

#[AsCommand(
    name: 'app:import:exam',
    description: 'Import exam results spreadsheet into application.',
)]
class ImportExamCommand extends Command
{
    public function __construct(
        private readonly ExamImporter $importer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('filename', InputArgument::REQUIRED, 'Absolute or relative location of exam spreadsheet.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $filename = $input->getArgument('filename');
        $filename = Path::isRelative($filename)
            ? Path::makeAbsolute($filename, getcwd())
            : Path::canonicalize($filename);

        if (!file_exists($filename) || !is_file($filename) || !is_readable($filename)) {
            $io->warning('Please provide a valid, local, readable file to import.');
            return Command::FAILURE;
        }

        try {
            $this->importer->import($filename);
        } catch (\Throwable) {
            $io->error('An error occured during import.');
            return Command::FAILURE;
        }

        $io->success('Exam imported!');
        return Command::SUCCESS;
    }
}
