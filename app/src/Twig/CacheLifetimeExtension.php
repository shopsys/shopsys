<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CacheLifetimeExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getCacheLifetimeByHours', [$this, 'getCacheLifetimeByHours']),
        ];
    }

    /**
     * @param int $hours
     * @return int
     */
    public function getCacheLifetimeByHours(int $hours): int
    {
        return 60 * 60 * $hours;
    }
}
