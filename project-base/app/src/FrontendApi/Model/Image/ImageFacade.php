<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Image;

use App\FrontendApi\Model\Image\ImageRepository as FrontendApiImageRepository;
use Shopsys\FrameworkBundle\Component\Image\ImageRepository;
use Shopsys\FrontendApiBundle\Component\Image\ImageFacade as BaseImageFacade;

class ImageFacade extends BaseImageFacade
{
    /**
     * @var \App\FrontendApi\Model\Image\ImageRepository
     */
    private FrontendApiImageRepository $frontendApiImageRepository;

    /**
     * @param \App\Component\Image\ImageRepository $imageRepository
     * @param \App\FrontendApi\Model\Image\ImageRepository $frontendApiImageRepository
     */
    public function __construct(ImageRepository $imageRepository, FrontendApiImageRepository $frontendApiImageRepository)
    {
        parent::__construct($imageRepository);

        $this->frontendApiImageRepository = $frontendApiImageRepository;
    }

    /**
     * @param int[] $entityIds
     * @param string $entityName
     * @param string|null $type
     * @return \App\Component\Image\Image[][]
     */
    public function getAllImagesIndexedByEntityId(array $entityIds, string $entityName, ?string $type): array
    {
        return $this->frontendApiImageRepository->getAllImagesIndexedByEntityId($entityIds, $entityName, $type);
    }

    /**
     * @param int[] $entityIds
     * @param string $entityName
     * @param string|null $type
     * @return \App\Component\Image\Image[]|null[]
     */
    public function getImagesByTypeAndPositionIndexedByEntityId(array $entityIds, string $entityName, ?string $type): array
    {
        return $this->frontendApiImageRepository->getImagesByTypeAndPositionIndexedByEntityId(
            $entityIds,
            $entityName,
            $type
        );
    }
}
