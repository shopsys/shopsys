<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToDeleteFile;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'shopsys:images:migrate')]
class MigrateImagesCommand extends Command
{
    protected const DIRECTORY_SEPARATOR_REGEX = '@[\\\\/]{1}@';

    /**
     * @param string $imageDirectory
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig
     */
    public function __construct(
        protected readonly string $imageDirectory,
        protected readonly FilesystemOperator $filesystem,
        protected readonly Setting $setting,
        protected readonly ImageConfig $imageConfig,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Remove all the folders with the generated image sizes, move images from the "original" folders one level up.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        if ($this->setting->get(Setting::IMAGE_STRUCTURE_MIGRATED_FOR_PROXY) === true) {
            $style->info('Image structure has been already migrated');

            return Command::SUCCESS;
        }

        $start = microtime(true);
        $style->info('Retrieving all image directories...');
        $directories = $this->getAllImageEntityDirectories();
        $style->table(['Found directories'], array_map(static fn (string $directory) => [$directory], $directories));

        $style->info('Removing all legacy image sizes directories...');
        $directoriesWithoutLegacySizesDirectories = $this->deleteAllLegacyImageSizesDirectories($directories, $style);

        $sortedDirectoriesWithoutLegacySizesDirectories = $this->sortDirectoriesFromDeepestLevel($directoriesWithoutLegacySizesDirectories);
        $sortedOriginalSizeDirectories = $this->getOriginalSizeDirectories($sortedDirectoriesWithoutLegacySizesDirectories);

        $style->info('Moving original files one directory up...');
        $this->moveFilesOneDirectoryUp($style, $sortedOriginalSizeDirectories);
        $style->info(sprintf('The image structure has been successfully migrated. Total time: %s seconds, memory usage: %sMB', microtime(true) - $start, memory_get_usage(true) / 1024 / 1024));

        $this->setting->set(Setting::IMAGE_STRUCTURE_MIGRATED_FOR_PROXY, true);

        return Command::SUCCESS;
    }

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $style
     * @param string[] $directories
     */
    protected function moveFilesOneDirectoryUp(SymfonyStyle $style, array $directories): void
    {
        foreach ($directories as $directory) {
            $style->writeln(sprintf('Moving files from %s to %s', $directory, str_replace('/original', '', $directory)));

            foreach ($this->filesystem->listContents($directory) as $fileInfo) {
                if ($fileInfo->isFile()) {
                    $this->filesystem->move($fileInfo->path(), str_replace('original/', '', $fileInfo->path()));
                }
            }
            $this->deleteDirectory($directory, $style);
        }
    }

    /**
     * @param string[] $directories
     * @param string $directoryToRemove
     * @return bool
     */
    protected function isDeepestDirectory(array $directories, string $directoryToRemove): bool
    {
        foreach ($directories as $directory) {
            if (dirname($directory) === $directoryToRemove) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return string[]
     */
    protected function getAllImageEntityDirectories(): array
    {
        $directories = [];
        $imageEntityNamesRegex = $this->getImageEntityNamesRegex();

        foreach ($this->listDirectoriesRecursive($this->imageDirectory) as $imageEntityDirectory) {
            if ($this->isImageEntityDirectory($imageEntityDirectory, $imageEntityNamesRegex)) {
                $directories[] = $imageEntityDirectory;
            }
        }

        return $directories;
    }

    /**
     * @param string $directory
     * @param string $imageEntityNamesRegex
     * @return bool
     */
    protected function isImageEntityDirectory(string $directory, string $imageEntityNamesRegex): bool
    {
        return preg_match($imageEntityNamesRegex, $directory) === 1;
    }

    /**
     * @param string[] $directories
     * @param \Symfony\Component\Console\Style\SymfonyStyle $style
     * @return string[]
     */
    protected function deleteAllLegacyImageSizesDirectories(array $directories, SymfonyStyle $style): array
    {
        foreach ($directories as $key => $directory) {
            if (basename($directory) === 'original') {
                continue;
            }

            $directoryCanBeRemoved = $this->isDeepestDirectory($directories, $directory);

            if (!$directoryCanBeRemoved) {
                continue;
            }

            $this->deleteDirectory($directory, $style);
            unset($directories[$key]);
        }

        return $directories;
    }

    /**
     * @param string[] $directories
     * @return string[]
     */
    private function sortDirectoriesFromDeepestLevel(array $directories): array
    {
        usort($directories, static function ($directoryA, $directoryB) {
            // the more directory separators there are in the path, the deeper the directory is
            return preg_match_all(static::DIRECTORY_SEPARATOR_REGEX, $directoryB) <=> preg_match_all(static::DIRECTORY_SEPARATOR_REGEX, $directoryA);
        });

        return $directories;
    }

    /**
     * @param string[] $directories
     * @return string[]
     */
    private function getOriginalSizeDirectories(array $directories): array
    {
        return array_filter($directories, static function ($directory) {
            return basename($directory) === 'original';
        });
    }

    /**
     * @return string
     */
    protected function getImageEntityNamesRegex(): string
    {
        $imageEntityNames = array_map(static fn (ImageEntityConfig $imageEntityConfig) => ($imageEntityConfig->getEntityName()), $this->imageConfig->getAllImageEntityConfigsByClass());

        return '@' . implode('|', $imageEntityNames) . '@';
    }

    /**
     * @param string $directory
     * @return string[]
     */
    protected function listDirectoriesRecursive(string $directory): array
    {
        $directories = [];

        foreach ($this->filesystem->listContents($directory) as $fileInfo) {
            if ($fileInfo->isDir()) {
                $directories[] = $fileInfo->path();
                $subDirectories = $this->listDirectoriesRecursive($fileInfo->path());
                $directories = [...$directories, ...$subDirectories];
            }
        }

        return $directories;
    }

    /**
     * @param string $directory
     * @param \Symfony\Component\Console\Style\SymfonyStyle $style
     */
    private function deleteDirectory(string $directory, SymfonyStyle $style): void
    {
        $isDirectoryEmpty = $this->filesystem->listContents($directory)->toArray() === [];
        $additionalInfo = $isDirectoryEmpty ? 'is empty' : 'with all its content';

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
