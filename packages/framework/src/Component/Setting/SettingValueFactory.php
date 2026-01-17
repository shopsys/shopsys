<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Setting;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class SettingValueFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \DateTimeInterface|string|int|float|bool|null $value
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
