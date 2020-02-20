<?php

namespace Shopsys\FrameworkBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CropZerosExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('cropZeros', [$this, 'cropZeros']),
        ];
    }

    /**
     * @param string $value
     * @return string
     */
    public function cropZeros($value)
    {
        return preg_replace('/(?:[,.]0+|([,.]\d*?)0+)$/', '$1', $value);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cropZeros';
    }
}
