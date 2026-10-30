<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFileData;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MustUploadFileValidator extends ConstraintValidator
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFileData|\Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData $value
     */
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof MustUploadFile) {
            throw new UnexpectedTypeException($constraint, MustUploadFile::class);
        }

        if (count($value->uploadedFiles) > 0 || $this->hasRemainingStoredFile($value)) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->addViolation();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFileData|\Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData $value
     */
    protected function hasRemainingStoredFile(mixed $value): bool
    {
        if ($value instanceof ImageUploadData) {
            $storedFiles = $value->orderedImages;
            $filesToDelete = $value->imagesToDelete;
        } elseif ($value instanceof AbstractUploadedFileData) {
            $storedFiles = $value->orderedFiles;
            $filesToDelete = $value->filesToDelete;
        } else {
            return false;
        }

        foreach ($storedFiles as $storedFile) {
            if (!in_array($storedFile, $filesToDelete, true)) {
                return true;
            }
        }

        return false;
    }
}
