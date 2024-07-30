<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use League\Flysystem\FilesystemOperator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'shopsys:uploaded-files:check-migrate',
    description: 'Checks if the uploaded files structure is compatible with "shopsys:uploaded-files:migrate" command.',
)]
class CheckMigrateUploadedFilesCommand extends Command
{
    /**
     * @param string $uploadedFilesDirectory
     * @param \League\Flysystem\FilesystemOperator $filesystem
     */
    public function __construct(
        protected readonly string $uploadedFilesDirectory,
        protected readonly FilesystemOperator $filesystem,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        $style->info('Checking current directory structure and files...');

        $errors = $this->checkDirectoriesAndFiles($this->uploadedFilesDirectory);

        if (count($errors) > 0) {
            $style->error('Current directory structure is incompatible for migration! See the following issues:');

            foreach ($errors as $error) {
                $style->writeln($error['message']);
                $style->table(['Paths'], array_map(static fn (string $path) => [$path], $error['paths']));
            }

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @param string $rootDirectory
     * @return array<int, array{message:string, paths: string[]}>
     */
    protected function checkDirectoriesAndFiles(string $rootDirectory): array
    {
        $directories = [];
        $files = [];
        $fileNames = [];

        foreach ($this->filesystem->listContents($rootDirectory) as $fileInfo) {
            if ($fileInfo->isDir()) {
                $directories[] = $fileInfo->path();

                continue;
            }

            $files[] = $fileInfo->path();
            $fileNames[] = basename($fileInfo->path());
        }

        if (count($directories) === 0) {
            return [];
        }

        $secondLevelDirectories = [];

        foreach ($directories as $firstLevel) {
            foreach ($this->filesystem->listContents($firstLevel) as $fileInfo) {
                if ($fileInfo->isDir()) {
                    $secondLevelDirectories[] = $fileInfo->path();
                }

                $files[] = $fileInfo->path();
                $fileNames[] = basename($fileInfo->path());
            }
        }

        $errors = [];

        if (count($secondLevelDirectories) > 0) {
            $errors[] = [
                'message' => 'Second level directories were found. Only one level of directories is allowed.',
                'paths' => $secondLevelDirectories,
            ];
        }

        $uniqueFileNames = array_unique($fileNames);

        if (count($fileNames) !== count($uniqueFileNames)) {
            $duplicates = array_diff_assoc($fileNames, $uniqueFileNames);
            $duplicateFiles = array_filter($files, static fn (string $file) => in_array(basename($file), $duplicates, true));

            $errors[] = [
                'message' => 'Duplicate file names were found. All file names must be unique.',
                'paths' => $duplicateFiles,
            ];
        }

        return $errors;
    }
}
