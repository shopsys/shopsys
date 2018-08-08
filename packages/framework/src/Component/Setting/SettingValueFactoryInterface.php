<?php

namespace Shopsys\FrameworkBundle\Component\Setting;

interface SettingValueFactoryInterface
{

    /**
     * @param \DateTime|string|int|float|bool|null $value
     */
    public function create(
        string $name,
        $value,
        int $domainId
    ): SettingValue;
}
