<?php

namespace Shopsys\FrameworkBundle\Component\Setting;

use DateTime;
use Shopsys\FrameworkBundle\Component\Money\Money;

interface SettingValueFactoryInterface
{
    /**
     * @param string $name
     * @param float|\DateTime|bool|int|string|\Shopsys\FrameworkBundle\Component\Money\Money|null $value
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Setting\SettingValue
     */
    public function create(
        string $name,
        float|DateTime|bool|int|string|Money|null $value,
        int $domainId
    ): SettingValue;
}
