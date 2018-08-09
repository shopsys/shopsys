<?php

namespace Shopsys\FrameworkBundle\Component\Setting;

class SettingValueFactory implements SettingValueFactoryInterface
{

    /**
     * @param \DateTime|string|int|float|bool|null $value
     */
    public function create(
        string $name,
        $value,
        int $domainId
    ): SettingValue {
        return new SettingValue($name, $value, $domainId);
    }
}
