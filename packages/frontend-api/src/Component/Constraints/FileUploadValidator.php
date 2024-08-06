<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Shopsys\FrontendApiBundle\Component\Constraints\Exception\FileUploadValidationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FileUploadValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof FileUpload) {
            throw new UnexpectedTypeException($constraint, FileUpload::class);
        }

        $uploadedFiles = (array)$value;

        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile */
        foreach ($uploadedFiles as $uploadedFile) {
            try {
                $this->checkUploadedFile($uploadedFile, $constraint);
                $this->checkFileSize($uploadedFile, $constraint);
                $this->checkMimeType($uploadedFile, $constraint);
            } catch (FileUploadValidationException) {
                continue;
            }
        }
    }

    /**
     * @param mixed $value
     * @param \Shopsys\FrontendApiBundle\Component\Constraints\FileUpload $constraint
     */
    protected function checkUploadedFile(mixed $value, FileUpload $constraint): void
    {
        if (!($value instanceof UploadedFile) || !$value->isValid()) {
            $this->context
                ->buildViolation($constraint->uploadErrorMessage)
                ->setCode(FileUpload::UPLOAD_ERROR)
                ->addViolation();

            throw new FileUploadValidationException();
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     * @param \Shopsys\FrontendApiBundle\Component\Constraints\FileUpload $constraint
     */
    protected function checkFileSize(UploadedFile $uploadedFile, FileUpload $constraint): void
    {
        if ($constraint->maxSize !== null && $uploadedFile->getSize() > $constraint->maxSize) {
            $this->context
                ->buildViolation(
                    $constraint->maxSizeMessage,
                    ['{{ fileName }}' => $uploadedFile->getClientOriginalName()],
                )
                ->setCode(FileUpload::TOO_BIG_ERROR)
                ->addViolation();

            throw new FileUploadValidationException();
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     * @param \Shopsys\FrontendApiBundle\Component\Constraints\FileUpload $constraint
     */
    protected function checkMimeType(UploadedFile $uploadedFile, FileUpload $constraint): void
    {
        $fileMimeType = $uploadedFile->getMimeType();

        $allowedMimeTypes = (array)$constraint->mimeTypes;

        foreach ($allowedMimeTypes as $allowedMimeType) {
            if ($fileMimeType === $allowedMimeType) {
                return;
            }

            $discrete = strstr($allowedMimeType, '/*', true);

            if ($discrete !== false && strstr($fileMimeType, '/', true) === $discrete) {
                return;
            }
        }

        $this->context
            ->buildViolation(
                $constraint->mimeTypesMessage,
                ['{{ fileName }}' => $uploadedFile->getClientOriginalName()],
            )
            ->setCode(FileUpload::MIMETYPE_ERROR)
            ->addViolation();

        throw new FileUploadValidationException();
    }
}
