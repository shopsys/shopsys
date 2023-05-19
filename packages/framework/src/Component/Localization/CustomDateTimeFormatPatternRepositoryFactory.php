<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Localization;

use IntlDateFormatter;

class CustomDateTimeFormatPatternRepositoryFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatPatternRepository
     */
    public function create(): DateTimeFormatPatternRepository
    {
        $customDateTimeFormatPatternRepository = new DateTimeFormatPatternRepository();
        $customDateTimeFormatPatternRepository->add(
            new DateTimeFormatPattern('y-MM-dd', 'en', IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE)
        );
        $customDateTimeFormatPatternRepository->add(
            new DateTimeFormatPattern('y-MM-dd, h:mm:ss a', 'en', IntlDateFormatter::MEDIUM, IntlDateFormatter::MEDIUM)
        );

        return $customDateTimeFormatPatternRepository;
    }
}
