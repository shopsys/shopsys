<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Advert\Advert;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrontendApiBundle\Component\Image\ImageFacade as FrontendApiImageFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ImagesQuery extends AbstractQuery
{
    protected const IMAGE_ENTITY_PRODUCT = 'product';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Component\Image\ImageFacade $frontendApiImageFacade
     */
    public function __construct(
        protected readonly ImageFacade $imageFacade,
        protected readonly ImageConfig $imageConfig,
        protected readonly Domain $domain,
        protected readonly FrontendApiImageFacade $frontendApiImageFacade,
    ) {
    }

    /**
     * @param object $entity
     * @param string|null $type
     * @return array
     */
    public function imagesByEntityQuery(object $entity, ?string $type): array
    {
        $entityName = $this->imageConfig->getEntityName($entity);

        return $this->resolveByEntityId($entity->getId(), $entityName, $type);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|array $data
     * @param string|null $type
     * @return array
     */
    public function imagesByProductQuery($data, ?string $type): array
    {
        $productId = $data instanceof Product ? $data->getId() : $data['id'];

        return $this->resolveByEntityId($productId, static::IMAGE_ENTITY_PRODUCT, $type);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\Advert $advert
     * @param string|null $type
     * @return array
     */
    public function imagesByAdvertQuery(Advert $advert, ?string $type): array
    {
        $entityName = $this->imageConfig->getEntityName($advert);

        return $this->getResolvedImages(
            $this->frontendApiImageFacade->getImagesByEntityIdAndNameIndexedById(
                $advert->getId(),
                $entityName,
                $type,
            ),
        );
    }

    /**
     * @param int $entityId
     * @param string $entityName
     * @param string|null $type
     * @return array
     */
    protected function resolveByEntityId(int $entityId, string $entityName, ?string $type): array
    {
        $images = $this->frontendApiImageFacade->getImagesByEntityIdAndNameIndexedById($entityId, $entityName, $type);

        return $this->getResolvedImages($images);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image[] $images
     * @return array<int, array{url: string, name: string|null}>
     */
    protected function getResolvedImages(array $images): array
    {
        $resolvedImages = [];

        foreach ($images as $image) {
            $resolvedImages[] = $this->getResolvedImage($image);
        }

        return $resolvedImages;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image $image
     * @return array{url: string, name: string|null}
     */
    protected function getResolvedImage(Image $image): array
    {
        return [
            'name' => $image->getName(),
            'url' => $this->imageFacade->getImageUrl(
                $this->domain->getCurrentDomainConfig(),
                $image,
                $image->getType(),
            ),
        ];
    }
}
