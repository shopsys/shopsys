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
    public function getFilters(): array
    {
        return [
            new TwigFilter('joinNoneEmpty', $this->getArray(...)),
        ];
    }

    public function getArray(array $array, mixed $glue = ', '): string
    {
        return implode($glue, array_filter($array));
    }

    public function getName(): string
    {
        return 'join_none_empty';
    }
}
