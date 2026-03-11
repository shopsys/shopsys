<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PhonePrefix;

readonly class CountryDialCode
{
    public function __construct(
        public string $code,
        public string $dialCode,
    ) {
    }
}
