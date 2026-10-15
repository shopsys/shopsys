<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Field;

use Override;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TextField extends AbstractField
{
    #[Override]
    protected mixed $default = '';

    #[Override]
    public function getFormType(): string
    {
        return TextType::class;
    }

    #[Override]
    public function normalizeValue(mixed $value): string
    {
        return is_scalar($value) ? trim((string)$value) : '';
    }

    #[Override]
    public function extractText(mixed $value): string
    {
        return $this->normalizeValue($value);
    }
}
