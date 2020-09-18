<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Image;

use Shopsys\FrameworkBundle\Component\Image\ImageRepository;

class ImageFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageRepository
     */
    protected $imageRepository;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageRepository $imageRepository
     */
    public function __construct(ImageRepository $imageRepository)
    {
        $this->imageRepository = $imageRepository;
    }

    /**
     * @param int $entityId
     * @param string $entityName
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function getImagesByEntityIdAndNameIndexedById(int $entityId, string $entityName, ?string $type): array
    {
        return $this->imageRepository->getImagesByEntityIndexedById(
            $entityName,
            $entityId,
            $type
        );
    }
}
