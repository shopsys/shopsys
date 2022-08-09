<?php

namespace Shopsys\FrameworkBundle\Component\Setting;

use DateTime;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Money\Money;

class SettingValueFactory implements SettingValueFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver
     */
    protected $entityNameResolver;

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(EntityNameResolver $entityNameResolver)
    {
        $this->entityNameResolver = $entityNameResolver;
    }

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
    ): SettingValue {
        $classData = $this->entityNameResolver->resolve(SettingValue::class);

        return new $classData($name, $value, $domainId);
    }
}
