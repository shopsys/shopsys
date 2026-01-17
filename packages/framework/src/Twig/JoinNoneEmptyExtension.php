<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class JoinNoneEmptyExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFilter[]
     */
    #[Override]
    public function getFilters()
    {
        return [
            new TwigFilter('joinNoneEmpty', $this->getArray(...)),
        ];
    }

    /**
     * @param mixed $glue
     * @return string
     */
    public function getArray(array $array, $glue = ', ')
    {
        return implode($glue, array_filter($array));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'join_none_empty';
    }
}
