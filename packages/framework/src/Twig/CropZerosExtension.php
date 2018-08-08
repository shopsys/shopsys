<?php

namespace Shopsys\FrameworkBundle\Twig;

use Twig_Extension;
use Twig_SimpleFilter;

class CropZerosExtension extends Twig_Extension
{
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('cropZeros', [$this, 'cropZeros']),
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
