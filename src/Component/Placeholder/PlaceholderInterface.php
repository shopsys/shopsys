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
    public function convert(string $text, ?string $locale);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getPattern();

    /**
     * @param string $text
     * @return mixed
     */
    public function isValidText(string $text);
}
