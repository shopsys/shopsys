<?php

declare(strict_types=1);

namespace App\Model\Product\Elasticsearch;

use App\Model\Product\Availability\ProductAvailabilityFacade;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository as BaseProductExportRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository;

/**
 * @property \App\Model\Product\ProductFacade $productFacade
 * @method int[] extractVariantIds(\App\Model\Product\Product $product)
 * @method string extractDetailUrl(int $domainId, \App\Model\Product\Product $product)
 * @method int[] extractFlags(\App\Model\Product\Product $product)
 * @method int[] extractCategories(int $domainId, \App\Model\Product\Product $product)
 * @method array extractParameters(string $locale, \App\Model\Product\Product $product)
 * @method array extractPrices(int $domainId, \App\Model\Product\Product $product)
 * @method array extractVisibility(int $domainId, \App\Model\Product\Product $product)
 * @property \App\Model\Product\Parameter\ParameterRepository $parameterRepository
 * @property \App\Model\Product\ProductVisibilityRepository $productVisibilityRepository
 */
class ProductExportRepository extends BaseProductExportRepository
{
    /**
     * @var \App\Model\Product\Availability\ProductAvailabilityFacade
     */
    private $productAvailabilityFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Product\ProductVisibilityRepository $productVisibilityRepository
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        ParameterRepository $parameterRepository,
        ProductFacade $productFacade,
        FriendlyUrlRepository $friendlyUrlRepository,
        Domain $domain,
        ProductVisibilityRepository $productVisibilityRepository,
        FriendlyUrlFacade $friendlyUrlFacade,
        ProductAvailabilityFacade $productAvailabilityFacade
    ) {
        parent::__construct(
            $em,
            $parameterRepository,
            $productFacade,
            $friendlyUrlRepository,
            $domain,
            $productVisibilityRepository,
            $friendlyUrlFacade
        );
        $this->productAvailabilityFacade = $productAvailabilityFacade;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @param string $locale
     * @return array
     */
    protected function extractResult(Product $product, int $domainId, string $locale): array
    {
        $flagIds = $this->extractFlags($product);
        $categoryIds = $this->extractCategories($domainId, $product);
        $parameters = $this->extractParameters($locale, $product);
        $prices = $this->extractPrices($domainId, $product);
        $visibility = $this->extractVisibility($domainId, $product);
        $detailUrl = $this->extractDetailUrl($domainId, $product);
        $variantIds = $this->extractVariantIds($product);
        $highPriceWithVat = $product->getHighPriceWithVat($domainId);

        return [
            'id' => $product->getId(),
            'catnum' => $product->getCatnum(),
            'partno' => $product->getPartno(),
            'ean' => $product->getEan(),
            'name' => $product->getName($locale),
            'description' => $product->getDescription($domainId),
            'short_description' => $product->getShortDescription($domainId),
            'brand' => $product->getBrand() ? $product->getBrand()->getId() : '',
            'flags' => $flagIds,
            'categories' => $categoryIds,
            'in_stock' => $this->productAvailabilityFacade->isProductAvailableOnDomain($product, $domainId),
            'prices' => $prices,
            'parameters' => $parameters,
            'ordering_priority' => $product->getOrderingPriority(),
            'calculated_selling_denied' => $product->getCalculatedSellingDenied(),
            'selling_denied' => $product->isSellingDenied(),
            'availability' => $this->productAvailabilityFacade->getProductAvailabilityInformationByDomainId($product, $domainId),
            'is_main_variant' => $product->isMainVariant(),
            'detail_url' => $detailUrl,
            'visibility' => $visibility,
            'uuid' => $product->getUuid(),
            'unit' => $product->getUnit()->getName($locale),
            'is_using_stock' => $product->isUsingStock(),
            'stock_quantity' => $this->productAvailabilityFacade->getGroupedStockQuantity($product, $domainId),
            'variants' => $variantIds,
            'main_variant_id' => $product->isVariant() ? $product->getMainVariant()->getId() : null,
            'name_prefix' => $product->getNamePrefix($locale),
            'name_sufix' => $product->getNameSufix($locale),
            'non_selling_price' => $highPriceWithVat === null ? null : $highPriceWithVat->getAmount(),
            'is_in_sale' => $product->isProductInSale(),
        ];
    }
}
