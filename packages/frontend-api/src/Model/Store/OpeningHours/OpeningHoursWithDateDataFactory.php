<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Store\OpeningHours;

use DateTimeImmutable;

class OpeningHoursWithDateDataFactory
{
    public function createForDate(DateTimeImmutable $date): OpeningHoursWithDateData
    {
        $openingHoursWithDateData = new OpeningHoursWithDateData();
        $openingHoursWithDateData->date = $date;
        $openingHoursWithDateData->dayOfWeek = (int)$date->format('N');

        return $openingHoursWithDateData;
    }
}
