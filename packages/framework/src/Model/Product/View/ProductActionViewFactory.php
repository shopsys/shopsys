<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\View;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Normalizer\Normalizer;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade;
use Symfony\Component\Serializer\Serializer;

/**
 * @experimental
 */
class ProductActionViewFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade
     */
    protected $productCollectionFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Normalizer\Normalizer
     */
    private $normalizer;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade $productCollectionFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Normalizer\Normalizer $normalizer
     */
    public function __construct(
        ProductCollectionFacade $productCollectionFacade,
        Domain $domain,
        Normalizer $normalizer
    ) {
        $this->productCollectionFacade = $productCollectionFacade;
        $this->domain = $domain;
        $this->normalizer = $normalizer;
    }

    /**
     * @param array $listedProductViewsData
     * @return \Shopsys\FrameworkBundle\Model\Product\View\ProductActionView[]
     */
    public function getProductActionViewsIndexedByProductIds(array $listedProductViewsData): array
    {
        $absoluteUrlsIndexedByProductId = $this->productCollectionFacade->getAbsoluteUrlsIndexedByProductId(
            $this->getAllIds($listedProductViewsData),
            $this->domain->getCurrentDomainConfig()
        );
        $productActionData = [];
        foreach ($listedProductViewsData as $listedProductViewData) {
            $productId = $listedProductViewData['id'];

            $productActionData[$productId] = [
                'id' => $productId,
                'sellingDenied' => $listedProductViewData['sellingDenied'],
                'mainVariant' => $listedProductViewData['mainVariant'],
                'detailUrl' => $absoluteUrlsIndexedByProductId[$productId],
            ];
        }

        return $this->normalizer->denormalizeArray($productActionData, $this->getViewObjectName());
    }

    /**
     * @return string
     */
    protected function getViewObjectName(): string
    {
        return ProductActionView::class;
    }

    /**
     * @param array $listedProductViewsData
     * @return int[]
     */
    protected function getAllIds(array $listedProductViewsData) {
        $allIds = [];
        foreach ($listedProductViewsData as $listedProductViewData) {
            $allIds[] = $listedProductViewData['id'];
        }

        return $allIds;
    }
}
