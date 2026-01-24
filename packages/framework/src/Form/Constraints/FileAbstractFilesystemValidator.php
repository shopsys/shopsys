<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use League\Flysystem\MountManager;
use League\Flysystem\UnableToCopyFile;
use Override;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File as FileObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\FileValidator;

class FileAbstractFilesystemValidator extends FileValidator
{
    public function __construct(
        protected readonly MountManager $mountManager,
        protected readonly FileUpload $fileUpload,
        protected readonly ParameterBagInterface $parameterBag,
    ) {
    }

    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        $abstractPath = $this->fileUpload->getTemporaryFilepath($value->getFilename());
        $localFileUniqueName = $this->fileUpload->getTemporaryFilepath(uniqid() . $value->getFilename());
        $localPath = $this->parameterBag->get('kernel.project_dir') . $localFileUniqueName;

        try {
            $this->mountManager->copy('main://' . $abstractPath, 'local://' . $localPath);
        } catch (UnableToCopyFile $e) {
            $this->context->buildViolation(
                'This file could not be found. Please remove it and try to upload it again.',
            )
                ->setCode((string)UPLOAD_ERR_NO_FILE)
                ->addViolation();

            return;
        }

        parent::validate(new FileObject($localPath), $constraint);

        $this->mountManager->delete('local://' . $localPath);
    }
}
