<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Twig;

use Shopsys\AdministrationBundle\Twig\Runtime\DateTimeAgoRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateTimeAgoExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('date_time_ago', [DateTimeAgoRuntime::class, 'dateTimeAgo']),
        ];
    }
}
