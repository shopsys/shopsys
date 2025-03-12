<?php

declare(strict_types=1);

namespace App\Component\Validator;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Override;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\FileValidator;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class FlysystemFileValidatorDecorator extends ConstraintValidator
{
    private const string LOCAL_TEMPORARY_DIRECTORY = 'localeFileUploads';

    /**
     * @param \Symfony\Component\Validator\Constraints\FileValidator $fileValidator
     * @param string $localTemporaryDir
     * @param \Symfony\Component\Filesystem\Filesystem $symfonyFilesystem
     * @param \League\Flysystem\MountManager $mountManager
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Shopsys\FrameworkBundle\Component\String\TransformStringHelper $transformStringHelper
     */
    public function __construct(
        private readonly FileValidator $fileValidator,
        private readonly string $localTemporaryDir,
        private readonly Filesystem $symfonyFilesystem,
        private readonly MountManager $mountManager,
        private readonly FilesystemOperator $filesystem,
        protected readonly TransformStringHelper $transformStringHelper,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if ($value instanceof File === false) {
            $this->context
                ->buildViolation(t('Unsupported file type'))
                ->addViolation();
        }

        if ($this->filesystem->has($value->getPathname()) === false) {
            $this->context
                ->buildViolation(t('File not found in main filesystem. Please try again later.'))
                ->addViolation();
        }

        $localPath = $this->getLocalTemporaryDirectory() . '/' . $value->getFilename();

        try {
            $this->mountManager->copy('main://' . $value->getPathname(), 'local://' . $this->transformStringHelper->removeDriveLetterFromPath($localPath));
            $this->fileValidator->validate(new File($localPath, false), $constraint);
        } finally {
            $this->symfonyFilesystem->remove($localPath);
        }
    }

    /**
     * @return string
     */
    private function getLocalTemporaryDirectory(): string
    {
        return $this->localTemporaryDir . '/' . self::LOCAL_TEMPORARY_DIRECTORY;
    }

    /**
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    #[Override]
    public function initialize(ExecutionContextInterface $context): void
    {
        $this->fileValidator->initialize($context);
    }
}
