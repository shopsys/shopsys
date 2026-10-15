<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Field;

use Override;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Validator\Constraints\Regex;

class UrlField extends AbstractField
{
    // "/(?!/)" accepts a relative path but not a protocol-relative "//host"
    protected const array ALLOWED_URL_START_PATTERNS = ['https?://', 'mailto:', 'tel:', '/(?!/)', '#'];

    protected const string VARIABLE_START_PATTERN = '\{[a-z0-9_]+\}';

    protected bool $variablesAllowed = false;

    public function allowVariables(): static
    {
        $this->variablesAllowed = true;

        return $this;
    }

    #[Override]
    public function getFormType(): string
    {
        return UrlType::class;
    }

    #[Override]
    public function getFormOptions(): array
    {
        // relative URLs are valid here, UrlType would prepend "http://" to them by default
        return parent::getFormOptions() + ['default_protocol' => null];
    }

    #[Override]
    public function getConstraints(): array
    {
        return [...parent::getConstraints(), new Regex(
            pattern: $this->getPattern(),
            message: 'Enter an http(s), mailto, tel or relative URL.',
        )];
    }

    #[Override]
    public function normalizeValue(mixed $value): mixed
    {
        if (!is_string($value) || preg_match($this->getPattern(), trim($value)) !== 1) {
            return $this->getDefault();
        }

        return trim($value);
    }

    protected function getPattern(): string
    {
        $startPatterns = static::ALLOWED_URL_START_PATTERNS;

        if ($this->variablesAllowed) {
            $startPatterns[] = static::VARIABLE_START_PATTERN;
        }

        return '~^(?:' . implode('|', $startPatterns) . ')\S*$~i';
    }
}
