<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Plugin;

use Shopsys\FrameworkBundle\Component\Plugin\Exception\PluginCrudExtensionAlreadyRegisteredException;
use Shopsys\FrameworkBundle\Component\Plugin\Exception\UnknownPluginCrudExtensionTypeException;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\Plugin\PluginCrudExtensionInterface;

class PluginCrudExtensionRegistry
{
    protected const array KNOWN_TYPES = [
        'product',
        'category',
        'stockSettings',
    ];

    /**
     * @var \Shopsys\Plugin\PluginCrudExtensionInterface[][]
     */
    protected array $crudExtensionsByTypeAndServiceId = [];

    public function __construct(
        protected readonly TransformStringHelper $transformStringHelper,
    ) {
    }

    public function registerCrudExtension(
        PluginCrudExtensionInterface $crudExtension,
        string $type,
        string $serviceId,
    ): void {
        self::assertTypeIsKnown($type);
        $key = $this->transformStringHelper->stringToCamelCase($serviceId);

        if (isset($this->crudExtensionsByTypeAndServiceId[$type][$key])) {
            throw new PluginCrudExtensionAlreadyRegisteredException($type, $key);
        }

        $this->crudExtensionsByTypeAndServiceId[$type][$key] = $crudExtension;
    }

    /**
     * @return \Shopsys\Plugin\PluginCrudExtensionInterface[]
     */
    public function getCrudExtensions(string $type): array
    {
        return $this->crudExtensionsByTypeAndServiceId[$type] ?? [];
    }

    public static function assertTypeIsKnown(string $type): void
    {
        if (!in_array($type, static::KNOWN_TYPES, true)) {
            throw new UnknownPluginCrudExtensionTypeException($type, static::KNOWN_TYPES);
        }
    }
}
