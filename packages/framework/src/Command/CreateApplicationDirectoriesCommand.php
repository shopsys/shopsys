<?php

namespace Shopsys\FrameworkBundle\Command;

use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\Image\DirectoryStructureCreator as ImageDirectoryStructureCreator;
use Shopsys\FrameworkBundle\Component\UploadedFile\DirectoryStructureCreator as UploadedFileDirectoryStructureCreator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class CreateApplicationDirectoriesCommand extends Command
{
    /**
     * @var array
     */
    protected $defaultInternalDirectories;

    /**
     * @var array
     */
    protected $defaultPublicDirectories;

    /**
     * @var array|null
     */
    protected $internalDirectories;

    /**
     * @var array|null
     */
    protected $publicDirectories;

    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:create-directories';

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $localFilesystem;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\DirectoryStructureCreator
     */
    protected $imageDirectoryStructureCreator;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\DirectoryStructureCreator
     */
    protected $uploadedFileDirectoryStructureCreator;

    /**
     * @param array $defaultInternalDirectories
     * @param array $defaultPublicDirectories
     * @param array|null $internalDirectories
     * @param array|null $publicDirectories
     * @param \League\Flysystem\FilesystemInterface $filesystem
     * @param \Symfony\Component\Filesystem\Filesystem $localFilesystem
     * @param \Shopsys\FrameworkBundle\Component\Image\DirectoryStructureCreator $imageDirectoryStructureCreator
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\DirectoryStructureCreator $uploadedFileDirectoryStructureCreator
     */
    public function __construct(
        $defaultInternalDirectories,
        $defaultPublicDirectories,
        $internalDirectories,
        $publicDirectories,
        FilesystemInterface $filesystem,
        Filesystem $localFilesystem,
        ImageDirectoryStructureCreator $imageDirectoryStructureCreator,
        UploadedFileDirectoryStructureCreator $uploadedFileDirectoryStructureCreator
    ) {
        $this->defaultInternalDirectories = $defaultInternalDirectories;
        $this->defaultPublicDirectories = $defaultPublicDirectories;
        $this->internalDirectories = $internalDirectories;
        $this->publicDirectories = $publicDirectories;
        $this->filesystem = $filesystem;
        $this->localFilesystem = $localFilesystem;
        $this->imageDirectoryStructureCreator = $imageDirectoryStructureCreator;
        $this->uploadedFileDirectoryStructureCreator = $uploadedFileDirectoryStructureCreator;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create application directories for locks, docs, content, images, uploaded files, etc.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->createMiscellaneousDirectories($output);
        $this->createImageDirectories($output);
        $this->createUploadedFileDirectories($output);
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function createMiscellaneousDirectories(OutputInterface $output)
    {
        $publicDirectories = $this->getPublicDirectories();
        $internalDirectories = $this->getInternalDirectories();

        foreach ($publicDirectories as $directory) {
            $this->filesystem->createDir($directory);
        }

        $this->localFilesystem->mkdir($internalDirectories);

        $output->writeln('<fg=green>Miscellaneous application directories were successfully created.</fg=green>');
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function createImageDirectories(OutputInterface $output)
    {
        $this->imageDirectoryStructureCreator->makeImageDirectories();

        $output->writeln('<fg=green>Directories for images were successfully created.</fg=green>');
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function createUploadedFileDirectories(OutputInterface $output)
    {
        $this->uploadedFileDirectoryStructureCreator->makeUploadedFileDirectories();

        $output->writeln('<fg=green>Directories for UploadedFile entities were successfully created.</fg=green>');
    }

    /**
     * return array
     */
    protected function getPublicDirectories()
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
    protected function getInternalDirectories()
    {
        $directories = $this->defaultInternalDirectories;

        if (is_array($this->internalDirectories)) {
            $directories = array_unique(array_merge($directories, $this->internalDirectories));
        }

        return $directories;
    }
}
