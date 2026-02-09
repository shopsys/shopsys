<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MustUploadFileValidator extends ConstraintValidator
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFileData $value
     */
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof MustUploadFile) {
            throw new UnexpectedTypeException($constraint, MustUploadFile::class);
        }

        if (count($value->uploadedFiles) === 0) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
