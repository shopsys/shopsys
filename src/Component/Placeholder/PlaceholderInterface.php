<?php

declare(strict_types = 1);

namespace App\Component\Placeholder;

interface PlaceholderInterface
{
    /**
     * @param string $text
     * @param string $locale
     * @return string
     */
    public function convert(string $text, ?string $locale): string;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getPattern(): string;
}
