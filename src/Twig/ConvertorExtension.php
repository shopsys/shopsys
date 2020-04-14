<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ConvertorExtension extends AbstractExtension
{
    private const DAYS_IN_WEEK = 7;

    /**
     * @var \Twig\Environment
     */
    protected $twigEnvironment;

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('daysToWeeks', [$this, 'convertDaysToWeeks']),
        ];
    }

    /**
     * @param int $days
     * @return int
     */
    public function convertDaysToWeeks(int $days): int
    {
        return (int)ceil($days / self::DAYS_IN_WEEK);
    }
}
