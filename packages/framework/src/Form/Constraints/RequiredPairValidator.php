<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RequiredPairValidator extends ConstraintValidator
{
    /**
     * @param mixed $values
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    #[Override]
    public function validate(mixed $values, Constraint $constraint): void
    {
        if (!$constraint instanceof RequiredPair) {
            throw new UnexpectedTypeException($constraint, RequiredPair::class);
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $value1 = $propertyAccessor->getValue($values, $constraint->field1);
        $value2 = $propertyAccessor->getValue($values, $constraint->field2);

        $label1 = $this->resolveFieldLabel($constraint->field1) ?? $constraint->field1;
        $label2 = $this->resolveFieldLabel($constraint->field2) ?? $constraint->field2;

        if ($value1 !== null && $value2 === null) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%field1%', (string)$label1)
                ->setParameter('%field2%', (string)$label2)
                ->atPath($constraint->field2)
                ->addViolation();
        }

        if ($value2 !== null && $value1 === null) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%field1%', (string)$label1)
                ->setParameter('%field2%', (string)$label2)
                ->atPath($constraint->field1)
                ->addViolation();
        }
    }

    /**
     * @param string $field
     * @return string|null
     */
    protected function resolveFieldLabel(string $field): ?string
    {
        $root = $this->context->getRoot();

        if ($root instanceof FormInterface && $root->has($field)) {
            $config = $root->get($field)->getConfig();
            $label = $config->getOption('label');

            return is_string($label) ? $label : null;
        }

        return null;
    }
}
