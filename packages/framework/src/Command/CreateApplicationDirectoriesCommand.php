<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use League\Flysystem\Config;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Visibility;
use Shopsys\FrameworkBundle\Component\Image\DirectoryStructureCreator as ImageDirectoryStructureCreator;
use Shopsys\FrameworkBundle\Component\UploadedFile\DirectoryStructureCreator as UploadedFileDirectoryStructureCreator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'shopsys:create-directories',
    description: 'Create application directories for locks, docs, content, images, uploaded files, etc.',
)]
class CreateApplicationDirectoriesCommand extends Command
{
    /**
     * @var string[]
     */
    private array $defaultInternalDirectories;

    /**
     * @var string[]
     */
    private array $defaultPublicDirectories;

    /**
     * @var string[]|null
     */
    private ?array $internalDirectories = null;

    /**
     * @var string[]|null
     */
    private ?array $publicDirectories = null;

    /**
     * @param array $defaultInternalDirectories
     * @param array $defaultPublicDirectories
     * @param array|null $internalDirectories
     * @param array|null $publicDirectories
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Symfony\Component\Filesystem\Filesystem $localFilesystem
     * @param \Shopsys\FrameworkBundle\Component\Image\DirectoryStructureCreator $imageDirectoryStructureCreator
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\DirectoryStructureCreator $uploadedFileDirectoryStructureCreator
     */
    public function __construct(
        $defaultInternalDirectories,
        $defaultPublicDirectories,
        $internalDirectories,
        $publicDirectories,
        private readonly FilesystemOperator $filesystem,
        private readonly Filesystem $localFilesystem,
        private readonly ImageDirectoryStructureCreator $imageDirectoryStructureCreator,
        private readonly UploadedFileDirectoryStructureCreator $uploadedFileDirectoryStructureCreator,
    ) {
        $this->defaultInternalDirectories = $defaultInternalDirectories;
        $this->defaultPublicDirectories = $defaultPublicDirectories;
        $this->internalDirectories = $internalDirectories;
        $this->publicDirectories = $publicDirectories;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->createMiscellaneousDirectories($output);
        $this->createImageDirectories($output);
        $this->createUploadedFileDirectories($output);

        return Command::SUCCESS;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    private function createMiscellaneousDirectories(OutputInterface $output)
    {
        $publicDirectories = $this->getPublicDirectories();
        $internalDirectories = $this->getInternalDirectories();

        foreach ($publicDirectories as $directory) {
            $this->filesystem->createDirectory($directory, [Config::OPTION_VISIBILITY => Visibility::PUBLIC]);
        }

        $this->localFilesystem->mkdir($internalDirectories);

        $output->writeln('<fg=green>Miscellaneous application directories were successfully created.</fg=green>');
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    private function createImageDirectories(OutputInterface $output)
    {
        $this->imageDirectoryStructureCreator->makeImageDirectories();

        $output->writeln('<fg=green>Directories for images were successfully created.</fg=green>');
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    private function createUploadedFileDirectories(OutputInterface $output)
    {
        $this->uploadedFileDirectoryStructureCreator->makeUploadedFileDirectories();

        $output->writeln('<fg=green>Directories for UploadedFile entities were successfully created.</fg=green>');
    }

    /**
     * return array
     */
    private function getPublicDirectories()
    {
        $directories = $this->defaultPublicDirectories;

        if (is_array($this->publicDirectories)) {
            $directories = array_unique(array_merge($directories, $this->publicDirectories));
        }

        return $directories;
    }

    /**
     * @return array
     */
    private function getInternalDirectories()
    {
        $directories = $this->defaultInternalDirectories;

        if (is_array($this->internalDirectories)) {
            $directories = array_unique(array_merge($directories, $this->internalDirectories));
        }

        return $directories;
    }
}
