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

    /**
     * @param \Shopsys\FrameworkBundle\Component\String\TransformStringHelper $transformStringHelper
     */
    public function __construct(
        protected readonly TransformStringHelper $transformStringHelper,
    ) {
    }

    /**
     * @param \Shopsys\Plugin\PluginCrudExtensionInterface $crudExtension
     * @param string $type
     * @param string $serviceId
     */
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
     * @param string $type
     * @return \Shopsys\Plugin\PluginCrudExtensionInterface[]
     */
    public function getCrudExtensions(string $type): array
    {
        return $this->crudExtensionsByTypeAndServiceId[$type] ?? [];
    }

    /**
     * @param string $type
     */
    public static function assertTypeIsKnown(string $type): void
    {
        if (!in_array($type, static::KNOWN_TYPES, true)) {
            throw new UnknownPluginCrudExtensionTypeException($type, static::KNOWN_TYPES);
        }
    }
}
