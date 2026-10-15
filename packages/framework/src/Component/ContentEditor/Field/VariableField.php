<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Field;

use Override;

/**
 * A `{placeholder}` string, e.g. `{product_image}`. The placeholders that can be chosen depend on the edited entity
 * (the mail template), so any well-formed placeholder is accepted and the form of the editor offers the actual ones.
 */
class VariableField extends ChoiceField
{
    public const string PLACEHOLDER_PATTERN = '~^\{[a-z0-9_]+\}$~i';

    #[Override]
    public function normalizeValue(mixed $value): mixed
    {
        return is_string($value) && preg_match(static::PLACEHOLDER_PATTERN, $value) === 1 ? $value : $this->getDefault();
    }
}
