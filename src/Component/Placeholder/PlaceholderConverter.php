<?php

declare(strict_types=1);

namespace App\Component\Placeholder;

use App\Component\Placeholder\Exception\PlaceholderAlreadyRegisteredException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class PlaceholderConverter
{
    /**
     * @var \App\Component\Placeholder\PlaceholderInterface[]
     */
    private $placeholdersByPlaceholderName = [];

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
        if (array_key_exists($placeholder->getName(), $this->placeholdersByPlaceholderName)) {
            throw PlaceholderAlreadyRegisteredException::create($placeholder);
        }

        $this->placeholdersByPlaceholderName[$placeholder->getName()] = $placeholder;
    }

    /**
     * @param string|null $text
     * @param null|string[] $allowedPlaceholders
     * @param string|null $locale
     * @return string
     */
    public function convert(?string $text, ?array $allowedPlaceholders = null, ?string $locale = null): string
    {
        if (empty($text)) {
            return '';
        }

        foreach ($this->placeholdersByPlaceholderName as $placeholder) {
            if (is_array($allowedPlaceholders) && !in_array($placeholder->getName(), $allowedPlaceholders, true)) {
                continue;
            }
            try {
                $text = $placeholder->convert($text, $locale);
            } catch (RouteNotFoundException $exception) {
            }
        }

        return $text;
    }
}
