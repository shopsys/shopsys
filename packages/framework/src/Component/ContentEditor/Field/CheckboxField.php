<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Field;

use Override;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class CheckboxField extends AbstractField
{
    #[Override]
    protected mixed $default = false;

    #[Override]
    public function getFormType(): string
    {
        return CheckboxType::class;
    }

    #[Override]
    public function normalizeValue(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
