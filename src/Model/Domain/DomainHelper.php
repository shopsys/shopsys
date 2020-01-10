<?php

declare(strict_types=1);


namespace App\Model\Domain;


class DomainHelper
{
    public const LOCATION_CS = 'cs';
    public const LOCATION_SK = 'sk';

    public const LOCALES = [
        self::LOCATION_CS,
        self::LOCATION_SK
    ];
}