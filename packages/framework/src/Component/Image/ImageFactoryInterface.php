<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image;

use Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig;

interface ImageFactoryInterface
{
    /**
     * @param string $entityName
     * @param int $entityId
     * @param array $namesIndexedByLocale
     * @param string $temporaryFilename
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image
     */
    public function create(
        string $entityName,
        int $entityId,
        array $namesIndexedByLocale,
        string $temporaryFilename,
        ?string $type,
    ): Image;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig $imageEntityConfig
     * @param int $entityId
     * @param array $names
     * @param array $temporaryFilenames
     * @param string|null $type
     * @return array
     */
    public function createMultiple(
        ImageEntityConfig $imageEntityConfig,
        int $entityId,
        array $names,
        array $temporaryFilenames,
        ?string $type,
    ): array;
}
