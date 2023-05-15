<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use League\Flysystem\MountManager;
use League\Flysystem\UnableToCopyFile;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File as FileObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\ImageValidator;

class ImageAbstractFilesystemValidator extends ImageValidator
{
    /**
     * @param \League\Flysystem\MountManager $mountManager
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag
     */
    public function __construct(
        protected readonly MountManager $mountManager,
        protected readonly FileUpload $fileUpload,
        protected readonly ParameterBagInterface $parameterBag,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $abstractPath = $this->fileUpload->getTemporaryFilepath($value->getFilename());
        $localFileUniqueName = $this->fileUpload->getTemporaryFilepath(uniqid() . $value->getFilename());
        $localPath = $this->parameterBag->get('kernel.project_dir') . $localFileUniqueName;

        try {
            $this->mountManager->copy('main://' . $abstractPath, 'local://' . $localPath);
        } catch (UnableToCopyFile $e) {
            $this->context->buildViolation(
                'This image could not be found. Please remove it and try to upload it again.',
            )
                ->setCode((string)UPLOAD_ERR_NO_FILE)
                ->addViolation();

            return;
        }

        parent::validate(new FileObject($localPath), $constraint);

        $this->mountManager->delete('local://' . $localPath);
    }
}
