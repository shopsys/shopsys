<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FileAllowedExtensionValidator extends ConstraintValidator
{
    /**
     * @param string|\Symfony\Component\HttpFoundation\File\File $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof FileAllowedExtension) {
            throw new UnexpectedTypeException($constraint, FileAllowedExtension::class);
        }

        if (!$value instanceof File) {
            throw new InvalidArgumentException('Value must be instance of ' . File::class);
        }

        if (!is_array($constraint->extensions)) {
            throw new ConstraintDefinitionException(
                'Extensions parameter of FileAllowedExtensionsValidator must be array.',
            );
        }

        if (!in_array(strtolower($value->getExtension()), $constraint->extensions, true)) {
            $this->context->addViolation(
                $constraint->message,
                [
                    '{{ value }}' => $this->formatValue($value->getExtension()),
                    '{{ extensions }}' => $this->formatValue(implode(', ', $constraint->extensions)),
                ],
            );
        }
    }
}
