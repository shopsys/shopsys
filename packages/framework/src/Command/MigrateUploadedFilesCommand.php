<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToDeleteFile;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'shopsys:uploaded-files:migrate',
    description: 'Migrates uploaded files directory structure. Backup your files before running this command.',
)]
class MigrateUploadedFilesCommand extends CheckMigrateUploadedFilesCommand
{
    /**
     * @param string $uploadedFilesDirectory
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(
        string $uploadedFilesDirectory,
        FilesystemOperator $filesystem,
        protected readonly Setting $setting,
    ) {
        parent::__construct($uploadedFilesDirectory, $filesystem);
    }

    protected function configure(): void
    {
        $this->addOption(
            'force',
            null,
            InputOption::VALUE_NONE,
            'Skip check and migrate files immediately. Use only if you are sure that the file structure is compatible. ',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        if ($this->setting->get(Setting::FILE_STRUCTURE_MIGRATED_FOR_RELATIONS) === true) {
            $style->info('File structure has been already migrated');
        }

        if (!$input->getOption('force')) {
            $check = parent::execute($input, $output);

            if ($check === Command::FAILURE) {
                return Command::FAILURE;
            }
        }

        $start = microtime(true);
        $style->info('Retrieving all files directories...');
        $directories = $this->listDirectories($this->uploadedFilesDirectory);

        if (count($directories) === 0) {
            $style->info('No directories found. The file structure is already migrated.');
            $this->setting->set(Setting::FILE_STRUCTURE_MIGRATED_FOR_RELATIONS, true);

            return Command::SUCCESS;
        }

        $style->table(['Found directories'], array_map(static fn (string $directory) => [$directory], $directories));

        $style->info('Moving files...');
        $this->moveFiles($style, $directories);
        $style->info(sprintf('The file structure has been successfully migrated. Total time: %s seconds, memory usage: %sMB', microtime(true) - $start, memory_get_usage(true) / 1024 / 1024));

        $this->setting->set(Setting::FILE_STRUCTURE_MIGRATED_FOR_RELATIONS, true);

        return Command::SUCCESS;
    }

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $style
     * @param string[] $directories
     */
    protected function moveFiles(SymfonyStyle $style, array $directories): void
    {
        $target = $this->uploadedFilesDirectory;

        foreach ($directories as $directory) {
            $style->writeln(sprintf('Moving files from %s to %s', $directory, $target));

            foreach ($this->filesystem->listContents($directory) as $fileInfo) {
                if ($fileInfo->isFile()) {
                    $fileName = basename($fileInfo->path());
                    $this->filesystem->move($fileInfo->path(), $target . '/' . $fileName);
                }
            }

            $this->deleteDirectory($directory, $style);
        }
    }

    /**
     * @param string $directory
     * @return string[]
     */
    protected function listDirectories(string $directory): array
    {
        $directories = [];

        foreach ($this->filesystem->listContents($directory) as $fileInfo) {
            if ($fileInfo->isDir()) {
                $directories[] = $fileInfo->path();
            }
        }

        return $directories;
    }

    /**
     * @param string $directory
     * @param \Symfony\Component\Console\Style\SymfonyStyle $style
     */
    protected function deleteDirectory(string $directory, SymfonyStyle $style): void
    {
        $isDirectoryEmpty = $this->filesystem->listContents($directory)->toArray() === [];
        $additionalInfo = $isDirectoryEmpty ? 'empty' : 'with all its content';

        $style->writeln(sprintf('Removing directory %s (%s)', $directory, $additionalInfo));

        try {
            if ($isDirectoryEmpty) {
                $this->filesystem->delete($directory . '/');
            } else {
                $this->filesystem->deleteDirectory($directory);
            }
        } catch (UnableToDeleteFile $exception) {
            // fallback for removing empty directories on local filesystem
            $this->filesystem->deleteDirectory($directory);
        }
    }
}
