<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Field;

use Override;
use Shopsys\FrameworkBundle\Form\ColorPickerType;

class ColorField extends AbstractField
{
    protected const string HEX_COLOR_PATTERN = '/^#(?:[0-9a-f]{3}|[0-9a-f]{6})$/i';

    #[Override]
    public function getFormType(): string
    {
        return ColorPickerType::class;
    }

    #[Override]
    public function normalizeValue(mixed $value): mixed
    {
        return is_string($value) && preg_match(static::HEX_COLOR_PATTERN, $value) === 1 ? $value : $this->getDefault();
    }
}
