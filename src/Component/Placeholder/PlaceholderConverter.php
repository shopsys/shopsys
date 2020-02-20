<?php

declare(strict_types = 1);

namespace App\Component\Placeholder;

use App\Component\Placeholder\Exception\PlaceholderAlreadyRegisteredException;

class PlaceholderConverter
{
    /**
     * @var \App\Component\Placeholder\PlaceholderInterface[]
     */
    private $placeholders = [];

    /**
     * @param \App\Component\Placeholder\PlaceholderInterface[] $placeholders
     */
    public function __construct(iterable $placeholders)
    {
        foreach ($placeholders as $placeholder) {
            $this->registerPlaceholder($placeholder);
        }
    }

    /**
     * @param \App\Component\Placeholder\PlaceholderInterface $placeholder
     */
    private function registerPlaceholder(PlaceholderInterface $placeholder): void
    {
        if (array_key_exists($placeholder->getName(), $this->placeholders)) {
            throw PlaceholderAlreadyRegisteredException::create($placeholder);
        }

        $this->placeholders[$placeholder->getName()] = $placeholder;
    }

    /**
     * @param string $text
     * @param null|string[] $allowedPlaceholders
     * @param string|null $locale
     * @return string
     */
    public function convert(string $text, $allowedPlaceholders = null, ?string $locale = null): string
    {
        if (empty($text)) {
            return $text;
        }

        foreach ($this->placeholders as $placeholder) {
            if (is_array($allowedPlaceholders) && !in_array($placeholder->getName(), $allowedPlaceholders, true)) {
                continue;
            }
            try {
                $text = $placeholder->convert($text, $locale);
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $exception) {
            }
        }

        return $text;
    }

    /**
     * @param string $text
     * @return string|null
     */
    public function resolvePlaceholderNameFromText(string $text): ?string
    {
        foreach ($this->placeholders as $placeholder) {
            if ($placeholder->isValidText($text)) {
                return $placeholder->getName();
            }
        }

        return null;
    }
}
