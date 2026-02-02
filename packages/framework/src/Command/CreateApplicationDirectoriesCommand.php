<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use League\Flysystem\Config;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Visibility;
use Override;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\DirectoryStructureCreator as CustomerUploadedFileDirectoryStructureCreator;
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
    public function __construct(
        private readonly FilesystemOperator $filesystem,
        private readonly Filesystem $localFilesystem,
        private readonly ImageDirectoryStructureCreator $imageDirectoryStructureCreator,
        private readonly UploadedFileDirectoryStructureCreator $uploadedFileDirectoryStructureCreator,
        private readonly CustomerUploadedFileDirectoryStructureCreator $customerUploadedFileDirectoryStructureCreator,
        private readonly array $defaultInternalDirectories,
        private readonly array $defaultPublicDirectories,
        private readonly ?array $internalDirectories = null,
        private readonly ?array $publicDirectories = null,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->createMiscellaneousDirectories($output);
        $this->createImageDirectories($output);
        $this->createUploadedFileDirectories($output);
        $this->createCustomerUploadedFileDirectories($output);

        return Command::SUCCESS;
    }

    private function createMiscellaneousDirectories(OutputInterface $output): void
    {
        $publicDirectories = $this->getPublicDirectories();
        $internalDirectories = $this->getInternalDirectories();

        foreach ($publicDirectories as $directory) {
            $this->filesystem->createDirectory($directory, [Config::OPTION_VISIBILITY => Visibility::PUBLIC]);
        }

        $this->localFilesystem->mkdir($internalDirectories);

        $output->writeln('<fg=green>Miscellaneous application directories were successfully created.</fg=green>');
    }

    private function createImageDirectories(OutputInterface $output): void
    {
        $this->imageDirectoryStructureCreator->makeImageDirectories();

        $output->writeln('<fg=green>Directories for images were successfully created.</fg=green>');
    }

    private function createUploadedFileDirectories(OutputInterface $output): void
    {
        $this->uploadedFileDirectoryStructureCreator->makeUploadedFileDirectories();

        $output->writeln('<fg=green>Directories for UploadedFile entities were successfully created.</fg=green>');
    }

    /**
     * @return string[]
     */
    private function getPublicDirectories(): array
    {
        $directories = $this->defaultPublicDirectories;

        if (is_array($this->publicDirectories)) {
            $directories = array_unique(array_merge($directories, $this->publicDirectories));
        }

        return $directories;
    }

    private function getInternalDirectories(): array
    {
        $directories = $this->defaultInternalDirectories;

        if (is_array($this->internalDirectories)) {
            $directories = array_unique(array_merge($directories, $this->internalDirectories));
        }

        return $directories;
    }

    protected function createCustomerUploadedFileDirectories(OutputInterface $output): void
    {
        $this->customerUploadedFileDirectoryStructureCreator->makeCustomerUploadedFileDirectories();

        $output->writeln('<fg=green>Directories for CustomerUploadedFile entities were successfully created.</fg=green>');
    }
}
