<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class NameFallbackExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFilter[]
     */
    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('nameWithFallbackOnEmpty', $this->getNameWithFallbackOnEmpty(...)),
        ];
    }

    public function getNameWithFallbackOnEmpty(?string $value, ?string $fallbackValue = null): string
    {
        if ($value === null || $value === '') {
            $value = $fallbackValue;
        }

        if ($value === null || $value === '') {
            return t('Name has not been entered in your current language');
        }

        return $value;
    }
}
