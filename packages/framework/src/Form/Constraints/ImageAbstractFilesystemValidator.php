<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File as FileObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\ImageValidator;

class ImageAbstractFilesystemValidator extends ImageValidator
{
    /**
     * @var \League\Flysystem\MountManager
     */
    protected $mountManager;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload
     */
    protected $fileUpload;

    /**
     * @var \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface
     */
    protected $parameterBag;

    /**
     * @param \League\Flysystem\MountManager $mountManager
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag
     */
    public function __construct(
        MountManager $mountManager,
        FileUpload $fileUpload,
        ParameterBagInterface $parameterBag
    ) {
        $this->mountManager = $mountManager;
        $this->fileUpload = $fileUpload;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        $abstractPath = $this->fileUpload->getTemporaryFilepath($value->getFilename());
        $localFileUniqueName = $this->fileUpload->getTemporaryFilepath(uniqid() . $value->getFilename());
        $localPath = $this->parameterBag->get('kernel.project_dir') . $localFileUniqueName;

        $copyResult = $this->mountManager->copy('main://' . $abstractPath, 'local://' . $localPath);

        if ($copyResult === false) {
            $this->context->buildViolation(
                'This image could not be found. Please remove it and try to upload it again.'
            )
                ->setCode((string)UPLOAD_ERR_NO_FILE)
                ->addViolation();
            return;
        }

        parent::validate(new FileObject($localPath), $constraint);

        $this->mountManager->delete('local://' . $localPath);
    }
}
