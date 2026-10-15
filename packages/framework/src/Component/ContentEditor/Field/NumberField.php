<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Field;

use Override;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class NumberField extends AbstractField
{
    #[Override]
    public function getFormType(): string
    {
        return NumberType::class;
    }

    #[Override]
    public function normalizeValue(mixed $value): mixed
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return str_contains($value, '.') ? (float)$value : (int)$value;
        }

        return $this->getDefault();
    }
}
