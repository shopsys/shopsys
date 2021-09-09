<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Products\DataMapper;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\FrontendApi\Exception\DeprecatedMethodException;
use App\FrontendApi\Model\Parameter\ParameterWithValuesFactory;
use App\Model\Product\Availability\ProductAvailabilityFacade;
use App\Model\Product\Parameter\ParameterRepository;
use App\Model\Product\Product;
use App\Model\Product\ProductFacade;
use App\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrontendApiBundle\Model\Product\ProductAccessoryFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductEntityFieldMapper as BaseProductEntityFieldMapper;

/**
 * @property \App\FrontendApi\Model\Parameter\ParameterWithValuesFactory $parameterWithValuesFactory
 * @method string|null getShortDescription(\App\Model\Product\Product $product)
 * @method string getLink(\App\Model\Product\Product $product)
 * @method \App\Model\Category\Category[] getCategories(\App\Model\Product\Product $product)
 * @method bool isSellingDenied(\App\Model\Product\Product $product)
 * @method \App\Model\Product\Product[] getAccessories(\App\Model\Product\Product $product)
 * @method string|null getDescription(\App\Model\Product\Product $product)
 * @method string|null getSeoH1(\App\Model\Product\Product $product)
 * @method string|null getSeoTitle(\App\Model\Product\Product $product)
 * @method string|null getSeoMetaDescription(\App\Model\Product\Product $product)
 */
class ProductEntityFieldMapper extends BaseProductEntityFieldMapper
{
    /**
     * @var \App\Model\Product\ProductFacade
     */
    private ProductFacade $productFacade;

    /**
     * @var \App\Model\Product\Availability\ProductAvailabilityFacade
     */
    private ProductAvailabilityFacade $productAvailabilityFacade;

    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @var \App\Model\Product\ProductRepository
     */
    private ProductRepository $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade
     */
    private PricingGroupSettingFacade $pricingGroupSettingFacade;

    /**
     * @var \App\Model\Product\Parameter\ParameterRepository
     */
    protected ParameterRepository $parameterRepository;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade $productCollectionFacade
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductAccessoryFacade $productAccessoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Parameter\ParameterWithValuesFactory $parameterWithValuesFactory
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     */
    public function __construct(
        Domain $domain,
        ProductCollectionFacade $productCollectionFacade,
        ProductAccessoryFacade $productAccessoryFacade,
        CurrentCustomerUser $currentCustomerUser,
        ParameterWithValuesFactory $parameterWithValuesFactory,
        ProductFacade $productFacade,
        ProductAvailabilityFacade $productAvailabilityFacade,
        FriendlyUrlFacade $friendlyUrlFacade,
        ProductRepository $productRepository,
        PricingGroupSettingFacade $pricingGroupSettingFacade,
        ParameterRepository $parameterRepository
    ) {
        parent::__construct(
            $domain,
            $productCollectionFacade,
            $productAccessoryFacade,
            $currentCustomerUser,
            $parameterWithValuesFactory
        );

        $this->productFacade = $productFacade;
        $this->productAvailabilityFacade = $productAvailabilityFacade;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->productRepository = $productRepository;
        $this->pricingGroupSettingFacade = $pricingGroupSettingFacade;
        $this->parameterRepository = $parameterRepository;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return bool
     */
    public function isUsingStock(Product $product): bool
    {
        return true;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return bool
     */
    public function isInSale(Product $product): bool
    {
        return $product->isProductInSale($this->domain->getId());
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return bool
     */
    public function hasSaleExclusion(Product $product): bool
    {
        return $product->getSaleExclusion($this->domain->getId());
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return string|null
     */
    public function getNameSuffix(Product $product): ?string
    {
        return $product->getNameSufix($this->domain->getLocale());
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return string|null
     */
    public function getPartNumber(Product $product): ?string
    {
        return $product->getPartno();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return string
     */
    public function getCatalogNumber(Product $product): string
    {
        return $product->getCatnum();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return array{name: string, status: string}
     */
    public function getExtendedAvailability(Product $product): array
    {
        return [
            'name' => $this->productAvailabilityFacade->getProductAvailabilityInformationByDomainId(
                $product,
                $this->domain->getId()
            ),
            'status' => $this->productAvailabilityFacade->getProductAvailabilityStatusByDomainId(
                $product,
                $this->domain->getId()
            ),
        ];
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     */
    public function getAvailability(BaseProduct $product): Availability
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return int
     */
    public function getStockQuantity(Product $product): int
    {
        return $this->productAvailabilityFacade->getGroupedStockQuantityByProductAndDomainId($product, $this->domain->getId());
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return int
     */
    public function getOrderingPriority(Product $product): int
    {
        return $product->getDomainOrderingPriority($this->domain->getId());
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\Flag\Flag[]
     */
    public function getFlags(Product $product): array
    {
        $flags = $product->getFlagsForDomain($this->domain->getId());

        $flagsIndexedById = [];
        foreach ($flags as $flag) {
            $flagsIndexedById[$flag->getId()] = $flag;
        }

        $variants = [];
        if ($product->isMainVariant() === true) {
            $variants = $this->productRepository->getAllSellableVariantsByMainVariant(
                $product,
                $this->domain->getId(),
                $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($this->domain->getId())
            );
        }

        foreach ($variants as $variant) {
            $variantFlags = $variant->getFlagsForDomain($this->domain->getId());
            foreach ($variantFlags as $variantFlag) {
                $flagsIndexedById[$variantFlag->getId()] = $variantFlag;
            }
        }

        return array_values($flagsIndexedById);
    }

    /**
     * Method is overridden, so it returns parameters for the variants too.
     *
     * @param \App\Model\Product\Product $product
     * @return array
     */
    public function getParameters(BaseProduct $product): array
    {
        $products = [];
        if ($product->isMainVariant() === true) {
            $products = $this->productRepository->getAllSellableVariantsByMainVariant(
                $product,
                $this->domain->getId(),
                $this->currentCustomerUser->getPricingGroup()
            );
        }
        $products[] = $product;

        $productParameterValuesData = $this->parameterRepository->getProductParameterValuesDataByProducts($products, $this->domain->getLocale());

        return $this->parameterWithValuesFactory->createParametersArrayFromProductArray(['parameters' => $productParameterValuesData]);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return string
     */
    public function getSlug(Product $product): string
    {
        $friendlyUrl = $this->friendlyUrlFacade->getMainFriendlyUrl(
            $this->domain->getId(),
            'front_product_detail',
            $product->getId()
        );

        return '/' . $friendlyUrl->getSlug();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return array
     */
    public function getFiles(Product $product): array
    {
        $downloadFiles = $this->productFacade->getDownloadFilesForProductByDomainConfig(
            $product,
            $this->domain->getDomainConfigById($this->domain->getId())
        );

        return array_map(
            static fn ($fileData) => [
                'anchorText' => $fileData['anchor_text'],
                'url' => $fileData['url'],
            ],
            $downloadFiles
        );
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return array<int, array{store_name: string, availability_information: string, exposed: bool, availability_status: string}>
     */
    public function getStoreAvailabilities(Product $product): array
    {
        $storeAvailabilitiesInformation = $this->productAvailabilityFacade->getProductStoresAvailabilitiesInformationByDomainIdIndexedByStoreId(
            $product,
            $this->domain->getId()
        );

        $result = [];
        foreach ($storeAvailabilitiesInformation as $storeAvailabilityInformation) {
            $result[] = [
                'store_name' => $storeAvailabilityInformation->getStoreName(),
                'availability_information' => $storeAvailabilityInformation->getAvailabilityInformation(),
                'exposed' => $storeAvailabilityInformation->isExposedProduct(),
                'availability_status' => $storeAvailabilityInformation->getAvailabilityStatus(),
            ];
        }

        return $result;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return int
     */
    public function getAvailableStoresCount(Product $product): int
    {
        return $this->productAvailabilityFacade->getAvailableStoresCount(
            $product,
            $this->domain->getId()
        );
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return int
     */
    public function getExposedStoresCount(Product $product): int
    {
        return $this->productAvailabilityFacade->getExposedStoresCount(
            $product,
            $this->domain->getId()
        );
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return string[]
     */
    public function getUsps(Product $product): array
    {
        return $product->getAllNonEmptyShortDescriptionUsp($this->domain->getId());
    }
}
