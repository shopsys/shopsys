<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Validation;

use Override;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ContentDocumentNotEmptyValidator extends ConstraintValidator
{
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ContentDocumentNotEmpty) {
            throw new UnexpectedTypeException($constraint, ContentDocumentNotEmpty::class);
        }

        if (!$value instanceof ContentDocument || $value->isEmpty()) {
            $this->context->addViolation($constraint->message);
        }
    }
}
