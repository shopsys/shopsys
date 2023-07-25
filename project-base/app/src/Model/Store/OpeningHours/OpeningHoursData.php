<?php

declare(strict_types=1);

namespace App\Model\Store\OpeningHours;

class OpeningHoursData
{
    public int $dayOfWeek;

    public ?string $firstOpeningTime = null;

    public ?string $firstClosingTime = null;

    public ?string $secondOpeningTime = null;

    public ?string $secondClosingTime = null;
}
