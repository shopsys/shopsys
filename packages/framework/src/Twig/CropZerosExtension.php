<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CropZerosExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFilter[]
     */
    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('cropZeros', $this->cropZeros(...)),
        ];
    }

    public function cropZeros(string $value): string
    {
        return preg_replace('/(?:[,.]0+|([,.]\d*?)0+)$/', '$1', $value);
    }

    public function getName(): string
    {
        return 'cropZeros';
    }
}
