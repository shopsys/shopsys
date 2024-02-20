<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Store\OpeningHours;

use DateTimeImmutable;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData;

class OpeningHoursWithDateData extends OpeningHoursData
{
    /**
     * @var \DateTimeImmutable
     */
    public DateTimeImmutable $date;
}
