<?php

namespace Shopsys\FrameworkBundle\Model\Product\Collection;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Image\ImageRepository;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductCollectionFacade
{
    protected ImageConfig $imageConfig;

    protected ImageRepository $imageRepository;

    protected ImageFacade $imageFacade;

    protected FriendlyUrlRepository $friendlyUrlRepository;

    protected ParameterRepository $parameterRepository;

    protected FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageRepository $imageRepository
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        ImageConfig $imageConfig,
        ImageRepository $imageRepository,
        ImageFacade $imageFacade,
        FriendlyUrlRepository $friendlyUrlRepository,
        ParameterRepository $parameterRepository,
        FriendlyUrlFacade $friendlyUrlFacade
    ) {
        $this->imageConfig = $imageConfig;
        $this->imageRepository = $imageRepository;
        $this->imageFacade = $imageFacade;
        $this->friendlyUrlRepository = $friendlyUrlRepository;
        $this->parameterRepository = $parameterRepository;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param string|null $sizeName
     * @return string[]
     */
    public function getImagesUrlsIndexedByProductId(array $products, DomainConfig $domainConfig, $sizeName = null)
    {
        $imagesUrlsByProductId = [];

        foreach ($this->getMainImagesIndexedByProductId($products) as $productId => $image) {
            if ($image === null) {
                $imagesUrlsByProductId[$productId] = null;
            } else {
                try {
                    $imagesUrlsByProductId[$productId] = $this->imageFacade->getImageUrl(
                        $domainConfig,
                        $image,
                        $sizeName
                    );
                } catch (ImageNotFoundException $e) {
                    $imagesUrlsByProductId[$productId] = null;
                }
            }
        }

        return $imagesUrlsByProductId;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]|null[]
     */
    protected function getMainImagesIndexedByProductId(array $products)
    {
        $productEntityName = $this->imageConfig->getImageEntityConfigByClass(Product::class)->getEntityName();
        $imagesByProductId = $this->imageRepository->getMainImagesByEntitiesIndexedByEntityId(
            $products,
            $productEntityName
        );

        $imagesOrNullByProductId = [];

        foreach ($products as $product) {
            if (array_key_exists($product->getId(), $imagesByProductId)) {
                $imagesOrNullByProductId[$product->getId()] = $imagesByProductId[$product->getId()];
            } else {
                $imagesOrNullByProductId[$product->getId()] = null;
            }
        }

        return $imagesOrNullByProductId;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[]|int[] $productsOrProductIds
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string[]
     */
    public function getAbsoluteUrlsIndexedByProductId(array $productsOrProductIds, DomainConfig $domainConfig)
    {
        $mainFriendlyUrlsByProductId = $this->friendlyUrlRepository->getMainFriendlyUrlsByEntitiesIndexedByEntityId(
            $productsOrProductIds,
            'front_product_detail',
            $domainConfig->getId()
        );

        $absoluteUrlsByProductId = [];

        foreach ($mainFriendlyUrlsByProductId as $productId => $friendlyUrl) {
            $absoluteUrlsByProductId[$productId] = $this->friendlyUrlFacade->getAbsoluteUrlByFriendlyUrl($friendlyUrl);
        }

        return $absoluteUrlsByProductId;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string[][]
     */
    public function getProductParameterValuesIndexedByProductIdAndParameterName(array $products, DomainConfig $domainConfig)
    {
        $locale = $domainConfig->getLocale();

        return $this->parameterRepository->getParameterValuesIndexedByProductIdAndParameterNameForProducts(
            $products,
            $locale
        );
    }
}
