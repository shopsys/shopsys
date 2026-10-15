<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Validation;

use Override;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ContentDocumentContainsVariableValidator extends ConstraintValidator
{
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ContentDocumentContainsVariable) {
            throw new UnexpectedTypeException($constraint, ContentDocumentContainsVariable::class);
        }

        // the whole stored data is searched: the variable may be in a text, a link, a variable field or a link in rich text markup
        if ($value instanceof ContentDocument && str_contains($value->toJson(), $constraint->variable)) {
            return;
        }

        $this->addViolation($constraint);
    }

    protected function addViolation(ContentDocumentContainsVariable $constraint): void
    {
        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ needle }}', $constraint->variable)
            ->addViolation();
    }
}
