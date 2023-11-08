<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config;

use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageTypeNotFoundException;
use Shopsys\FrameworkBundle\Component\Utils\Utils;

class ImageEntityConfig
{
    public const WITHOUT_NAME_KEY = '__NULL__';

    /**
     * @param string $entityName
     * @param string $entityClass
     * @param array $types
     * @param array $multipleByType
     */
    public function __construct(
        protected readonly string $entityName,
        protected readonly string $entityClass,
        protected readonly array $types,
        protected readonly array $multipleByType,
    ) {
    }

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @return string[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @param string|null $type
     * @return bool
     */
    public function isMultiple(?string $type): bool
    {
        $key = Utils::ifNull($type, self::WITHOUT_NAME_KEY);

        if (array_key_exists($key, $this->multipleByType)) {
            return $this->multipleByType[$key];
        }

        throw new ImageTypeNotFoundException($this->entityClass, $type);
    }
}
