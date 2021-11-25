<?php

declare(strict_types=1);

namespace App\Component\Placeholder\Placeholder;

use App\Component\Placeholder\Exception\PlaceholderConversionErrorException;
use App\Component\Placeholder\PlaceholderInterface;

abstract class AbstractPlaceholder implements PlaceholderInterface
{
    /**
     * @inheritdoc
     */
    public function convert(string $text, ?string $locale = null): string
    {
        $replaceCallback = function ($matches) use ($locale) {
            return $this->replace($matches, $locale);
        };

        $result = preg_replace_callback($this->getPattern(), $replaceCallback, $text);
        if ($result === null) {
            throw PlaceholderConversionErrorException::create($this, $text);
        }

        return $result;
    }

    /**
     * @param array $matches
     * @param string|null $locale
     * @return string
     */
    abstract protected function replace(array $matches, ?string $locale): string;
}
