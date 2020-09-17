<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Image;

interface ImageViewFacadeInterface
{
    /**
     * @param string $entityClass
     * @param int[] $entityIds
     * @return \Shopsys\ReadModelBundle\Image\ImageView[]|null[]
     */
    public function getMainImagesByEntityIds(string $entityClass, array $entityIds): array;

    /**
     * @param string $entityClass
     * @param int $entityId
     * @return \Shopsys\ReadModelBundle\Image\ImageView[]
     */
    public function getAllImagesByEntityId(string $entityClass, int $entityId): array;
}
