<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class NameFallbackExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('nameWithFallbackOnEmpty', $this->getNameWithFallbackOnEmpty(...)),
        ];
    }

    /**
     * @param string|null $value
     * @param string|null $fallbackValue
     * @return string
     */
    public function getNameWithFallbackOnEmpty(?string $value, ?string $fallbackValue = null): string
    {
        if ($value === null || $value === '') {
            $value = $fallbackValue;
        }

        if ($value === null || $value === '') {
            return t('Name in default language is not entered');
        }

        return $value;
    }
}
