<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PhonePrefix\Settings;

class PhonePrefixSettingsDataFactory
{
    public function create(): PhonePrefixSettingsData
    {
        return new PhonePrefixSettingsData();
    }
}
