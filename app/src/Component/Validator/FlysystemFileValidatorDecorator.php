<?php

declare(strict_types=1);

namespace App\Component\Validator;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\FileValidator;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class FlysystemFileValidatorDecorator extends ConstraintValidator
{
    private const LOCAL_TEMPORARY_DIRECTORY = 'localeFileUploads';

    /**
     * @var \Symfony\Component\Validator\Constraints\FileValidator
     */
    private $fileValidator;

    /**
     * @var string
     */
    private $localTemporaryDir;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $symfonyFilesystem;

    /**
     * @var \League\Flysystem\MountManager
     */
    private $mountManager;

    /**
     * @var \League\Flysystem\FilesystemOperator
     */
    private $filesystem;

    /**
     * @param \Symfony\Component\Validator\Constraints\FileValidator $fileValidator
     * @param string $localTemporaryDir
     * @param \Symfony\Component\Filesystem\Filesystem $symfonyFilesystem
     * @param \League\Flysystem\MountManager $mountManager
     * @param \League\Flysystem\FilesystemOperator $filesystem
     */
    public function __construct(
        FileValidator $fileValidator,
        string $localTemporaryDir,
        Filesystem $symfonyFilesystem,
        MountManager $mountManager,
        FilesystemOperator $filesystem
    ) {
        $this->fileValidator = $fileValidator;
        $this->symfonyFilesystem = $symfonyFilesystem;
        $this->mountManager = $mountManager;
        $this->filesystem = $filesystem;
        $this->localTemporaryDir = $localTemporaryDir;
    }

    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value === null || $value === '') {
            return;
        }

        if ($value instanceof File === false) {
            $this->context
                ->buildViolation(t('Nepodporovaný datový typ pro validaci souboru. Kontaktujte, prosím, správce obchodu.'))
                ->addViolation();
        }

        if ($this->filesystem->has($value->getPathname()) === false) {
            $this->context
                ->buildViolation(t('Soubor se nepodařilo nalézt na hlavním uložišti. Zkuste to, prosím, znovu nebo kontaktujte správce obchodu.'))
                ->addViolation();
        }

        $localPath = $this->getLocalTemporaryDirectory() . '/' . $value->getFilename();

        try {
            $this->mountManager->copy('main://' . $value->getPathname(), 'local://' . TransformString::removeDriveLetterFromPath($localPath));
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
    public function initialize(ExecutionContextInterface $context)
    {
        $this->fileValidator->initialize($context);
    }
}
