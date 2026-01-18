<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueCollectionValidator extends ConstraintValidator
{
    /**
     * @param array<int, mixed> $values
     */
    #[Override]
    public function validate(mixed $values, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueCollection) {
            throw new UnexpectedTypeException($constraint, UniqueCollection::class);
        }

        if ($constraint->fields !== null && !is_array($constraint->fields)) {
            throw new InvalidOptionsException(
                'Option "fields" must be array or null',
                ['fields'],
            );
        }

        foreach ($values as $index1 => $value1) {
            foreach ($values as $index2 => $value2) {
                if ($index1 !== $index2) {
                    if ($this->areValuesEqual($constraint, $value1, $value2)) {
                        $this->context->addViolation($constraint->message);

                        return;
                    }
                }
            }
        }
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     */
    protected function areValuesEqual(UniqueCollection $constraint, $value1, $value2): bool
    {
        if ($constraint->allowEmpty) {
            if ($value1 === null || $value2 === null) {
                return false;
            }
        }

        if ($constraint->fields === null) {
            return $value1 === $value2;
        }

        return $this->areValuesEqualInFields($constraint->fields, $value1, $value2);
    }

    /**
     * @param array<int, string> $fields
     * @param mixed $value1
     * @param mixed $value2
     */
    protected function areValuesEqualInFields(array $fields, $value1, $value2): bool
    {
        foreach ($fields as $field) {
            $fieldValue1 = $this->getFieldValue($value1, $field);
            $fieldValue2 = $this->getFieldValue($value2, $field);

            if ($fieldValue1 !== $fieldValue2) {
                return false;
            }
        }

        return true;
    }

    protected function getFieldValue(mixed $value, string $field): mixed
    {
        return PropertyAccess::createPropertyAccessor()->getValue($value, $field);
    }
}
