<?php

namespace Shopsys\FrameworkBundle\Twig;

use Twig_Extension;
use Twig_SimpleFilter;

class JoinNoneEmptyExtension extends Twig_Extension
{

    /**
     * @return \Twig_SimpleFilter[]
     */
    public function getFilters(): array
    {
        return [
            new Twig_SimpleFilter('joinNoneEmpty', [$this, 'getArray']),
        ];
    }

    public function getArray(array $array, $glue = ', '): string
    {
        return implode($glue, array_filter($array));
    }

    public function getName(): string
    {
        return 'join_none_empty';
    }
}
