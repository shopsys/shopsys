<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Setting;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class SettingValueFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param string $name
     * @param \DateTimeInterface|string|int|float|bool|null $value
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Setting\SettingValue
     */
    public function create(
        string $name,
        $value,
        int $domainId,
    ): SettingValue {
        $entityClassName = $this->entityNameResolver->resolve(SettingValue::class);

        return new $entityClassName($name, $value, $domainId);
    }
}
