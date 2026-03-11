<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PhonePrefix\Settings;

class PhonePrefixSettingsData
{
    /**
     * @var string[]
     */
    public $enabledCodes = [];

    /**
     * @var string|null
     */
    public $defaultCode;
}
