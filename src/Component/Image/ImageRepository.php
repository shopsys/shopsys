<?php

declare(strict_types=1);

namespace App\Component\Image;

use Shopsys\FrameworkBundle\Component\Image\ImageRepository as BaseImageRepository;

class ImageRepository extends BaseImageRepository
{
    /**
     * @param string $entityName
     * @param int $entityId
     * @param string $akeneoImageType
     * @return \App\Component\Image\Image|null
     */
    public function findImageByEntityForAkeneoImageType(string $entityName, int $entityId, string $akeneoImageType): ?Image
    {
        $image = $this->getImageRepository()->findOneBy(
            [
                'entityName' => $entityName,
                'entityId' => $entityId,
                'akeneoImageType' => $akeneoImageType,
            ],
            [
                'position' => 'asc',
                'id' => 'asc',
            ]
        );

        return $image;
    }
}
