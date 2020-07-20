<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Component\Router\CategorySeoMix\CategorySeoMixUrlGenerator;
use App\Component\SeoHelper\SeoHelper;
use App\Component\UploadedFile\UploadedFileFacade;
use App\Form\Front\Product\ProductFilterFormType;
use App\Model\Category\Category;
use App\Model\Category\CategoryFacade;
use App\Model\Category\CategoryParameterFacade;
use App\Model\Category\CategoryProductSeries\CategoryProductSeriesFacade;
use App\Model\CategorySeo\Exception\UnableToFindReadyCategorySeoMixException;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use App\Model\Gtm\GtmFacade;
use App\Model\Product\Availability\ProductAvailabilityFacade;
use App\Model\Product\Brand\Brand;
use App\Model\Product\Filter\ProductFilterData;
use App\Model\Product\Filter\ProductFilterFacade;
use App\Model\Product\Filter\ProductVariantFilterFacade;
use App\Model\Product\Listed\ListedProductViewElasticFacade;
use App\Model\Product\Package\ProductPackageFacade;
use App\Model\Product\Parameter\ParameterFacade;
use App\Model\Product\Product;
use App\Model\Product\ProductFacade;
use App\Model\Product\Series\ProductSeriesFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Model\Module\ModuleFacade;
use Shopsys\FrameworkBundle\Model\Module\ModuleList;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForBrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForListFacade;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForSearchFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Shopsys\FrameworkBundle\Twig\RequestExtension;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductController extends FrontBaseController
{
    public const SEARCH_TEXT_PARAMETER = 'q';
    public const PAGE_QUERY_PARAMETER = 'page';
    public const PRODUCTS_PER_PAGE = 36;
    private const PARAMETER_VALUE_ENTITY_NAME = 'parameterValue';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory
     */
    private $productFilterConfigFactory;

    /**
     * @var \App\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Model\Product\ProductOnCurrentDomainElasticFacade
     */
    private $productOnCurrentDomainFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Twig\RequestExtension
     */
    private $requestExtension;

    /**
     * @var \App\Model\Product\Listing\ProductListOrderingModeForListFacade
     */
    private $productListOrderingModeForListFacade;

    /**
     * @var \App\Model\Product\Listing\ProductListOrderingModeForBrandFacade
     */
    private $productListOrderingModeForBrandFacade;

    /**
     * @var \App\Model\Product\Listing\ProductListOrderingModeForSearchFacade
     */
    private $productListOrderingModeForSearchFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Module\ModuleFacade
     */
    private $moduleFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade
     */
    private $brandFacade;

    /**
     * @var \App\Model\Product\Listed\ListedProductViewElasticFacade
     */
    private $listedProductViewFacade;

    /**
     * @var \App\Model\Product\Availability\ProductAvailabilityFacade
     */
    private $productAvailabilityFacade;

    /**
     * @var \App\Model\Product\ProductFacade
     */
    private $productFacade;

    /**
     * @var \App\Model\Category\CategoryProductSeries\CategoryProductSeriesFacade
     */
    private $categoryProductSeriesFacade;

    /**
     * @var \App\Model\CategorySeo\ReadyCategorySeoMixFacade
     */
    private $readyCategorySeoMixFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade
     */
    private $seoSettingFacade;

    /**
     * @var \App\Model\Category\CategoryParameterFacade
     */
    private $categoryParameterFacade;

    /**
     * @var \App\Model\Product\Series\ProductSeriesFacade
     */
    private $productSeriesFacade;

    /**
     * @var \App\Model\Product\Package\ProductPackageFacade
     */
    private $productPackageFacade;

    /**
     * @var \App\Component\Router\CategorySeoMix\CategorySeoMixUrlGenerator
     */
    private $categorySeoMixUrlGenerator;

    /**
     * @var \App\Component\UploadedFile\UploadedFileFacade
     */
    private $uploadedFileFacade;

    /**
     * @var \App\Component\SeoHelper\SeoHelper
     */
    private $seoHelper;

    /**
     * @var \App\Model\Product\Parameter\ParameterFacade
     */
    private $parameterFacade;

    /**
     * @var \App\Model\Product\Filter\ProductFilterFacade
     */
    private $productFilterFacade;

    /**
     * @var \App\Model\Gtm\GtmFacade
     */
    private $gtmFacade;

    /**
     * @var \App\Model\Product\Filter\ProductVariantFilterFacade
     */
    private $productVariantFilterFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Twig\RequestExtension $requestExtension
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory $productFilterConfigFactory
     * @param \App\Model\Product\Listing\ProductListOrderingModeForListFacade $productListOrderingModeForListFacade
     * @param \App\Model\Product\Listing\ProductListOrderingModeForBrandFacade $productListOrderingModeForBrandFacade
     * @param \App\Model\Product\Listing\ProductListOrderingModeForSearchFacade $productListOrderingModeForSearchFacade
     * @param \Shopsys\FrameworkBundle\Model\Module\ModuleFacade $moduleFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     * @param \App\Model\Product\Listed\ListedProductViewElasticFacade $listedProductViewFacade
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \App\Model\Category\CategoryProductSeries\CategoryProductSeriesFacade $categoryProductSeriesFacade
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     * @param \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade $seoSettingFacade
     * @param \App\Model\Category\CategoryParameterFacade $categoryParameterFacade
     * @param \App\Model\Product\Series\ProductSeriesFacade $productSeriesFacade
     * @param \App\Model\Product\Package\ProductPackageFacade $productPackageFacade
     * @param \App\Component\Router\CategorySeoMix\CategorySeoMixUrlGenerator $categorySeoMixUrlGenerator
     * @param \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \App\Component\SeoHelper\SeoHelper $seoHelper
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \App\Model\Product\Filter\ProductFilterFacade $productFilterFacade
     * @param \App\Model\Gtm\GtmFacade $gtmFacade
     * @param \App\Model\Product\Filter\ProductVariantFilterFacade $productVariantFilterFacade
     */
    public function __construct(
        RequestExtension $requestExtension,
        CategoryFacade $categoryFacade,
        Domain $domain,
        ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade,
        ProductFilterConfigFactory $productFilterConfigFactory,
        ProductListOrderingModeForListFacade $productListOrderingModeForListFacade,
        ProductListOrderingModeForBrandFacade $productListOrderingModeForBrandFacade,
        ProductListOrderingModeForSearchFacade $productListOrderingModeForSearchFacade,
        ModuleFacade $moduleFacade,
        BrandFacade $brandFacade,
        ListedProductViewElasticFacade $listedProductViewFacade,
        ProductAvailabilityFacade $productAvailabilityFacade,
        ProductFacade $productFacade,
        CategoryProductSeriesFacade $categoryProductSeriesFacade,
        ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
        SeoSettingFacade $seoSettingFacade,
        CategoryParameterFacade $categoryParameterFacade,
        ProductSeriesFacade $productSeriesFacade,
        ProductPackageFacade $productPackageFacade,
        CategorySeoMixUrlGenerator $categorySeoMixUrlGenerator,
        UploadedFileFacade $uploadedFileFacade,
        SeoHelper $seoHelper,
        ParameterFacade $parameterFacade,
        ProductFilterFacade $productFilterFacade,
        GtmFacade $gtmFacade,
        ProductVariantFilterFacade $productVariantFilterFacade
    ) {
        $this->requestExtension = $requestExtension;
        $this->domain = $domain;
        $this->productOnCurrentDomainFacade = $productOnCurrentDomainFacade;
        $this->productFilterConfigFactory = $productFilterConfigFactory;
        $this->productListOrderingModeForListFacade = $productListOrderingModeForListFacade;
        $this->productListOrderingModeForBrandFacade = $productListOrderingModeForBrandFacade;
        $this->productListOrderingModeForSearchFacade = $productListOrderingModeForSearchFacade;
        $this->moduleFacade = $moduleFacade;
        $this->brandFacade = $brandFacade;
        $this->listedProductViewFacade = $listedProductViewFacade;
        $this->categoryFacade = $categoryFacade;
        $this->productAvailabilityFacade = $productAvailabilityFacade;
        $this->productFacade = $productFacade;
        $this->categoryProductSeriesFacade = $categoryProductSeriesFacade;
        $this->readyCategorySeoMixFacade = $readyCategorySeoMixFacade;
        $this->seoSettingFacade = $seoSettingFacade;
        $this->categoryParameterFacade = $categoryParameterFacade;
        $this->productSeriesFacade = $productSeriesFacade;
        $this->productPackageFacade = $productPackageFacade;
        $this->categorySeoMixUrlGenerator = $categorySeoMixUrlGenerator;
        $this->uploadedFileFacade = $uploadedFileFacade;
        $this->seoHelper = $seoHelper;
        $this->parameterFacade = $parameterFacade;
        $this->productFilterFacade = $productFilterFacade;
        $this->gtmFacade = $gtmFacade;
        $this->productVariantFilterFacade = $productVariantFilterFacade;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     */
    public function detailAction(Request $request, $id)
    {
        $requestPage = $request->get(self::PAGE_QUERY_PARAMETER);
        if (!$this->isRequestPageValid($requestPage)) {
            return $this->redirectToRoute('front_product_detail', $this->getRequestParametersWithoutPage());
        }
        $page = $requestPage === null ? 1 : (int)$requestPage;

        /** @var \App\Model\Product\Product $productVariant */
        $productVariant = $this->productOnCurrentDomainFacade->getVisibleProductById($id);

        if ($productVariant->isMainVariant()) {
            return $this->redirectToRoute('front_product_detail', ['id' => $productVariant->getDefaultVariant()->getId()], 301);
        } elseif ($productVariant->isVariant()) {
            $product = $productVariant->getMainVariant();
        } else {
            $product = $productVariant;
        }

        $this->gtmFacade->onProductDetailPage($product);

        //parts build from main product
        $accessories = $this->listedProductViewFacade->getAllAccessories($product->getId());
        $variants = $this->productOnCurrentDomainFacade->getVariantsForProduct($product);
        $productMainCategory = $this->categoryFacade->getProductMainCategoryByDomainId($product, $this->domain->getId());
        $categoryList = $this->categoryFacade->getAllProductCategoriesByProductAndDomainId($product, $this->domain->getId());
        $productAvailabilityInformation = $this->productAvailabilityFacade->getProductAvailabilityInformationByDomainId($product, $this->domain->getId());
        $productAvailabilityStatus = $this->productAvailabilityFacade->getProductAvailabilityStatusByDomainId($product, $this->domain->getId());
        $productAvailableStocksCountInformation = $this->productAvailabilityFacade->getProductAvailableStocksCountInformationByDomainId($product, $this->domain->getId());
        $productCountExposedInStores = $this->productAvailabilityFacade->getProductCountExposedInStocksInformationByDomainId($product, $this->domain->getId());
        $productStocksAvailabilitiesInformation = $this->productAvailabilityFacade->getProductStocksAvailabilitiesInformationByDomainIdIndexedByStockId($product, $this->domain->getId());
        $downloadFiles = $this->productFacade->getDownloadFilesForProductByDomain($product, $this->domain);

        $paginatedSimilarProducts = $this->listedProductViewFacade->getSimilarPaginatedProductsFormProductInCategory(
            $product,
            $this->domain->getId(),
            ProductListOrderingConfig::ORDER_BY_PRIORITY,
            $page,
            self::PRODUCTS_PER_PAGE
        );
        $this->productVariantFilterFacade->setupDefaultVariantsInPaginationResult($paginatedSimilarProducts);

        $productSeriesList = $this->productSeriesFacade->getAllVisibleByProductAndDomainId($product, $this->domain);
        $productSeriesProducts = [];
        foreach ($productSeriesList as $productSeries) {
            $productSeriesProducts[$productSeries->getId()] = $this->listedProductViewFacade->getAvailableProductsByProductSeries($productSeries);
        }

        $productPackages = $this->productPackageFacade->getProductPackagesByProduct($product);

        return $this->render('Front/Content/Product/detail.html.twig', [
            'product' => $productVariant,
            'accessories' => $accessories,
            'variants' => $variants,
            'productMainCategory' => $productMainCategory,
            'categoryList' => $categoryList,
            'domain' => $this->domain,
            'productAvailabilityInformation' => $productAvailabilityInformation,
            'productAvailabilityStatus' => $productAvailabilityStatus,
            'productStocksAvailabilitiesInformation' => $productStocksAvailabilitiesInformation,
            'productAvailableStocksCountInformation' => $productAvailableStocksCountInformation,
            'productCountExposedInStores' => $productCountExposedInStores,
            'downloadFiles' => $downloadFiles,
            'paginatedSimilarProducts' => $paginatedSimilarProducts,
            'productSeriesList' => $productSeriesList,
            'productSeriesProductsIndexedByProductSeries' => $productSeriesProducts,
            'productPackages' => $productPackages,
        ]);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function variantParametersAction(Product $product): Response
    {
        if ($product->isVariant()) {
            $mainProduct = $product->getMainVariant();
        } else {
            return new Response();
        }

        $currentVariantParameterValuesIndexedByParameterId = $this->parameterFacade
            ->getParameterValuesIndexedByParameterIdForProductVariant($product, $mainProduct->getVariantParameters(), $this->domain->getLocale());
        $currentVariantSetup = $this->parameterFacade->getParameterValueIdIndexedByParameterId($currentVariantParameterValuesIndexedByParameterId);
        $currentVariantSetupKey = $this->parameterFacade->getVariantSetupKey($currentVariantParameterValuesIndexedByParameterId);

        $variantSetupKeyMap = $this->parameterFacade->getVariantSetupKeyMapByMainProduct($mainProduct, $this->domain->getLocale(), $this->domain->getId());

        $parameterValuesIndexedByParameterId = $this->parameterFacade
            ->getParameterValuesIndexedByParameterIdForMainProduct($mainProduct, $this->domain->getLocale());

        return $this->render('Front/Content/Product/variantParameters.html.twig', [
            'mainProduct' => $mainProduct,
            'variant' => $product,
            'parameterValuesIndexedByParameterId' => $parameterValuesIndexedByParameterId,
            'variantParameterValuesIndexedByParameterId' => $currentVariantParameterValuesIndexedByParameterId,
            'currentVariantSetup' => $currentVariantSetup,
            'currentVariantSetupKey' => $currentVariantSetupKey,
            'variantSetupKeyMap' => $variantSetupKeyMap,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @param int|null $readyCategorySeoMixId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function listByCategoryAction(Request $request, int $id, ?int $readyCategorySeoMixId = null)
    {
        $category = $this->categoryFacade->getVisibleOnDomainById($this->domain->getId(), $id);
        $readyCategorySeoMix = $this->findReadyCategorySeoMix($readyCategorySeoMixId, $request, $category);
        $request->attributes->set('isCategorySeoMix', $readyCategorySeoMix === null ? false : true);

        $requestPage = $request->get(self::PAGE_QUERY_PARAMETER);
        if (!$this->isRequestPageValid($requestPage)) {
            return $this->redirectToRoute('front_product_list', $this->getRequestParametersWithoutPage());
        }

        $page = $requestPage === null ? 1 : (int)$requestPage;

        $disableIndexingBySeznamBot = $this->seoHelper->disableIndexingBySeznamBot($request, $page);

        $orderingModeId = $this->productListOrderingModeForListFacade->getOrderingModeIdFromRequest(
            $request,
            $readyCategorySeoMix
        );

        $productFilterData = new ProductFilterData();
        $productFilterConfig = $this->createProductFilterConfigForCategory($category);
        $filterForm = $this->createForm(ProductFilterFormType::class, $productFilterData, [
            'product_filter_config' => $productFilterConfig,
        ]);
        $filterForm->handleRequest($request);

        $paginationResult = $this->listedProductViewFacade->getFilteredPaginatedInCategory(
            $id,
            $productFilterData,
            $orderingModeId,
            $page,
            self::PRODUCTS_PER_PAGE
        );

        $this->productVariantFilterFacade->setupMostValuableVariantsInPaginationResultByProductFilterData($paginationResult, $productFilterData);

        $productFilterCountData = null;
        if ($this->moduleFacade->isEnabled(ModuleList::PRODUCT_FILTER_COUNTS)) {
            $productFilterCountData = $this->productOnCurrentDomainFacade->getCachedProductFilterCountDataInCategory(
                $id,
                $productFilterConfig,
                $productFilterData,
                $readyCategorySeoMix
            );
        }

        $productFilterFormRequestData = ($request->query->has('product_filter_form')) ? $request->query->get('product_filter_form') : [];
        $productFilterSetup = $this->productFilterFacade->getProductFilterSetupByProductFilterFormRequestData($productFilterFormRequestData);

        $this->gtmFacade->onProductListByCategoryPage($category, $paginationResult->getResults());

        $allParameterValuesImageFilePathsIndexedById = $this->uploadedFileFacade->getAllUploadedFilesFilePathByEntityName(self::PARAMETER_VALUE_ENTITY_NAME);

        $productListOrderingConfig = $this->productListOrderingModeForListFacade->getProductListOrderingConfig();
        $orderingModeId = $orderingModeId ?? $productListOrderingConfig->getDefaultOrderingModeId();
        $orderModeName = $productListOrderingConfig->getSupportedOrderingModesNamesIndexedById()[$orderingModeId];

        $viewParameters = [
            'paginationResult' => $paginationResult,
            'productFilterCountData' => $productFilterCountData,
            'category' => $category,
            'filterForm' => $filterForm->createView(),
            'filterFormSubmitted' => $filterForm->isSubmitted(),
            'visibleChildren' => $this->categoryFacade->getAllVisibleChildrenByCategoryAndDomainId($category, $this->domain->getId()),
            'priceRange' => $productFilterConfig->getPriceRange(),
            'categoryProductSeries' => $this->categoryProductSeriesFacade->getVisibleProductSeriesByCategoryAndDomainId($category, $this->domain->getId()),
            'readyCategorySeoMixId' => $readyCategorySeoMix === null ? null : $readyCategorySeoMix->getId(),
            'filterCollapsedParameters' => $this->categoryParameterFacade->getParametersCollapsedIndexedByIdForCategory($category),
            'allParameterValuesImagesIndexedById' => $allParameterValuesImageFilePathsIndexedById,
            'disableIndexingBySeznamBot' => $disableIndexingBySeznamBot,
            'productFilterSetup' => $productFilterSetup,
            'orderModeName' => $orderModeName,
        ];

        $viewParameters = array_merge(
            $viewParameters,
            $this->getAdditionalSeoViewParameters(
                $category,
                $paginationResult,
                $readyCategorySeoMix
            )
        );

        if ($request->isXmlHttpRequest()) {
            $viewParameters['url'] = $this->categorySeoMixUrlGenerator->generateUrlWithFallbackToProductList(
                $category->getId(),
                $request->query->all(),
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            return $this->render('Front/Content/Product/ajaxList.html.twig', $viewParameters);
        } else {
            $response = $this->render('Front/Content/Product/list.html.twig', $viewParameters);

            // Direct access on SeoMixUrl with ordering lost ordering after change in filter - This prevent it
            if ($readyCategorySeoMix !== null && $readyCategorySeoMix->getOrdering() !== null) {
                // The cookie must have httpOnly=false, because It is edited by JS
                $cookie = Cookie::create(
                    $productListOrderingConfig->getCookieName(),
                    $readyCategorySeoMix->getOrdering(),
                    0,
                    '/',
                    null,
                    null,
                    false
                );
                $response->headers->setCookie($cookie);
            }

            return $response;
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     */
    public function listByBrandAction(Request $request, $id)
    {
        $requestPage = $request->get(self::PAGE_QUERY_PARAMETER);
        if (!$this->isRequestPageValid($requestPage)) {
            return $this->redirectToRoute('front_brand_detail', $this->getRequestParametersWithoutPage());
        }
        $page = $requestPage === null ? 1 : (int)$requestPage;

        $disableIndexingBySeznamBot = $this->seoHelper->disableIndexingBySeznamBot($request, $page);

        $orderingModeId = $this->productListOrderingModeForBrandFacade->getOrderingModeIdFromRequest(
            $request
        );

        $paginationResult = $this->listedProductViewFacade->getPaginatedForBrand(
            $id,
            $orderingModeId,
            $page,
            self::PRODUCTS_PER_PAGE
        );
        /** @var \App\Model\Product\Brand\Brand $brand */
        $brand = $this->brandFacade->getById($id);

        $viewParameters = [
            'paginationResult' => $paginationResult,
            'brand' => $brand,
            'disableIndexingBySeznamBot' => $disableIndexingBySeznamBot,
        ];

        $viewParameters = array_merge(
            $viewParameters,
            $this->getAdditionalBrandSeoViewParameters(
                $brand,
                $paginationResult
            )
        );

        if ($request->isXmlHttpRequest()) {
            return $this->render('Front/Content/Product/ajaxListByBrand.html.twig', $viewParameters);
        } else {
            return $this->render('Front/Content/Product/listByBrand.html.twig', $viewParameters);
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function searchAction(Request $request)
    {
        $searchText = $request->query->get(self::SEARCH_TEXT_PARAMETER, '');

        $requestPage = $request->get(self::PAGE_QUERY_PARAMETER);
        if (!$this->isRequestPageValid($requestPage)) {
            return $this->redirectToRoute('front_product_search', $this->getRequestParametersWithoutPage());
        }
        $page = $requestPage === null ? 1 : (int)$requestPage;

        $orderingModeId = $this->productListOrderingModeForSearchFacade->getOrderingModeIdFromRequest(
            $request
        );

        $productFilterData = new ProductFilterData();

        $productFilterConfig = $this->createProductFilterConfigForSearch($searchText);
        $filterForm = $this->createForm(ProductFilterFormType::class, $productFilterData, [
            'product_filter_config' => $productFilterConfig,
        ]);
        $filterForm->handleRequest($request);

        $paginationResult = $this->listedProductViewFacade->getFilteredPaginatedForSearch(
            $searchText,
            $productFilterData,
            $orderingModeId,
            $page,
            self::PRODUCTS_PER_PAGE
        );

        $this->productVariantFilterFacade->setupMostValuableVariantsInPaginationResultByProductFilterData($paginationResult, $productFilterData);

        $productFilterCountData = null;
        if ($this->moduleFacade->isEnabled(ModuleList::PRODUCT_FILTER_COUNTS)) {
            $productFilterCountData = $this->productOnCurrentDomainFacade->getProductFilterCountDataForSearch(
                $searchText,
                $productFilterConfig,
                $productFilterData
            );
        }

        $productFilterFormRequestData = ($request->query->has('product_filter_form')) ? $request->query->get('product_filter_form') : [];
        $productFilterSetup = $this->productFilterFacade->getProductFilterSetupByProductFilterFormRequestData($productFilterFormRequestData);

        $allParameterValuesImageFilePathsIndexedById = $this->uploadedFileFacade->getAllUploadedFilesFilePathByEntityName(self::PARAMETER_VALUE_ENTITY_NAME);

        $productListOrderingConfig = $this->productListOrderingModeForSearchFacade->getProductListOrderingConfig();
        $orderingModeId = $orderingModeId ?? $productListOrderingConfig->getDefaultOrderingModeId();
        $orderModeName = $productListOrderingConfig->getSupportedOrderingModesNamesIndexedById()[$orderingModeId];

        $viewParameters = [
            'paginationResult' => $paginationResult,
            'productFilterCountData' => $productFilterCountData,
            'filterForm' => $filterForm->createView(),
            'filterFormSubmitted' => $filterForm->isSubmitted(),
            'searchText' => $searchText,
            'SEARCH_TEXT_PARAMETER' => self::SEARCH_TEXT_PARAMETER,
            'priceRange' => $productFilterConfig->getPriceRange(),
            'filterCollapsedParameters' => [],
            'allParameterValuesImagesIndexedById' => $allParameterValuesImageFilePathsIndexedById,
            'productFilterSetup' => $productFilterSetup,
            'orderModeName' => $orderModeName,
        ];

        $viewParameters = array_merge(
            $viewParameters,
            $this->getAdditionalSearchSeoViewParameters(
                $searchText,
                $paginationResult
            )
        );

        if ($request->isXmlHttpRequest()) {
            return $this->render('Front/Content/Product/ajaxSearch.html.twig', $viewParameters);
        } else {
            $viewParameters['foundCategories'] = $this->searchCategories($searchText);
            return $this->render('Front/Content/Product/search.html.twig', $viewParameters);
        }
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult $paginationResult
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     * @return string[]
     */
    private function getAdditionalSeoViewParameters(
        Category $category,
        PaginationResult $paginationResult,
        ?ReadyCategorySeoMix $readyCategorySeoMix
    ): array {
        $domainId = $this->domain->getId();

        $categoryUrl = $this->generateUrl('front_product_list', ['id' => $category->getId()]);
        if ($readyCategorySeoMix === null) {
            $seoH1 = $category->getSeoH1($domainId);
            $description = $category->getDescription($domainId);
            $shortDescription = $category->getShortDescription($domainId);
            $seoTitle = $category->getSeoTitle($domainId);
            $seoMetaDescription = $category->getSeoMetaDescription($domainId);
        } else {
            $seoH1 = $readyCategorySeoMix->getH1();
            $description = $readyCategorySeoMix->getDescription() ?? $category->getDescription($domainId);
            $shortDescription = $readyCategorySeoMix->getShortDescription() ?? $category->getShortDescription($domainId);
            $seoTitle = $readyCategorySeoMix->getTitle() ?? $seoH1;
            $seoMetaDescription = $readyCategorySeoMix->getMetaDescription() ?? $category->getSeoMetaDescription($domainId);
        }

        if ($seoMetaDescription === null) {
            $seoMetaDescription = $this->seoSettingFacade->getDescriptionMainPage($domainId);
        }

        if ($seoTitle === null) {
            $seoTitle = $category->getName();
        }

        if ($seoH1 === null) {
            $seoH1 = $category->getName();
        }

        return [
            'seoH1' => $this->seoHelper->addH1SeoPagination($seoH1, $paginationResult),
            'description' => $description,
            'shortDescription' => $shortDescription,
            'seoTitle' => $this->seoHelper->addTitleSeoPagination($seoTitle, $paginationResult),
            'seoMetaDescription' => $seoMetaDescription,
            'categoryUrl' => $categoryUrl,
        ];
    }

    /**
     * @param \App\Model\Product\Brand\Brand $brand
     * @param \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult $paginationResult
     * @return string[]
     */
    private function getAdditionalBrandSeoViewParameters(
        Brand $brand,
        PaginationResult $paginationResult
    ): array {
        $domainId = $this->domain->getId();

        return [
            'seoTitle' => $this->seoHelper->addTitleSeoPagination($brand->getSeoTitle($domainId) ?? $brand->getName(), $paginationResult),
            'seoH1' => $this->seoHelper->addH1SeoPagination($brand->getSeoH1($domainId) ?? $brand->getName(), $paginationResult),
        ];
    }

    /**
     * @param string $searchText
     * @param \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult $paginationResult
     * @return string[]
     */
    private function getAdditionalSearchSeoViewParameters(
        string $searchText,
        PaginationResult $paginationResult
    ): array {
        $seoTitle = t('Search results for "%searchText%"', ['%searchText%' => $searchText]);
        $seoH1 = t('Search results for "%searchText%"', ['%searchText%' => $searchText]);

        return [
            'seoTitle' => $this->seoHelper->addTitleSeoPagination($seoTitle, $paginationResult),
            'seoH1' => $this->seoHelper->addH1SeoPagination($seoH1, $paginationResult),
        ];
    }

    /**
     * @param \App\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    private function createProductFilterConfigForCategory(Category $category)
    {
        return $this->productFilterConfigFactory->createForCategory(
            $this->domain->getId(),
            $this->domain->getLocale(),
            $category
        );
    }

    /**
     * @param string|null $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    private function createProductFilterConfigForSearch($searchText)
    {
        return $this->productFilterConfigFactory->createForSearch(
            $this->domain->getId(),
            $this->domain->getLocale(),
            $searchText
        );
    }

    /**
     * @param string|null $searchText
     * @return \App\Model\Category\Category[]
     */
    private function searchCategories($searchText)
    {
        /** @var \App\Model\Category\Category[] $categories */
        $categories = $this->categoryFacade->getVisibleByDomainAndSearchText(
            $this->domain->getId(),
            $this->domain->getLocale(),
            $searchText
        );

        return $categories;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int|null $readyCategorySeoMixId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function selectOrderingModeForListAction(Request $request, ?int $readyCategorySeoMixId = null)
    {
        $productListOrderingConfig = $this->productListOrderingModeForListFacade->getProductListOrderingConfig();
        $readyCategorySeoMix = null;
        if ($readyCategorySeoMixId !== null) {
            $readyCategorySeoMix = $this->readyCategorySeoMixFacade->getById($readyCategorySeoMixId);
        }

        $orderingModeId = $this->productListOrderingModeForListFacade->getOrderingModeIdFromRequest(
            $request,
            $readyCategorySeoMix
        );

        return $this->render('Front/Content/Product/orderingSetting.html.twig', [
            'orderingModesNames' => $productListOrderingConfig->getSupportedOrderingModesNamesIndexedById(),
            'activeOrderingModeId' => $orderingModeId,
            'cookieName' => $productListOrderingConfig->getCookieName(),
            'isReadyCategorySeoMixPage' => $readyCategorySeoMixId !== null,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function selectOrderingModeForListByBrandAction(Request $request)
    {
        $productListOrderingConfig = $this->productListOrderingModeForBrandFacade->getProductListOrderingConfig();

        $orderingModeId = $this->productListOrderingModeForBrandFacade->getOrderingModeIdFromRequest(
            $request
        );

        return $this->render('Front/Content/Product/orderingSetting.html.twig', [
            'orderingModesNames' => $productListOrderingConfig->getSupportedOrderingModesNamesIndexedById(),
            'activeOrderingModeId' => $orderingModeId,
            'cookieName' => $productListOrderingConfig->getCookieName(),
            'isReadyCategorySeoMixPage' => false,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function selectOrderingModeForSearchAction(Request $request)
    {
        $productListOrderingConfig = $this->productListOrderingModeForSearchFacade->getProductListOrderingConfig();

        $orderingModeId = $this->productListOrderingModeForSearchFacade->getOrderingModeIdFromRequest(
            $request
        );

        return $this->render('Front/Content/Product/orderingSetting.html.twig', [
            'orderingModesNames' => $productListOrderingConfig->getSupportedOrderingModesNamesIndexedById(),
            'activeOrderingModeId' => $orderingModeId,
            'cookieName' => $productListOrderingConfig->getCookieName(),
            'isReadyCategorySeoMixPage' => false,
        ]);
    }

    /**
     * @param string|null $page
     * @return bool
     */
    private function isRequestPageValid($page)
    {
        return $page === null || (preg_match('@^([2-9]|[1-9][0-9]+)$@', $page));
    }

    /**
     * @return array
     */
    private function getRequestParametersWithoutPage()
    {
        $parameters = $this->requestExtension->getAllRequestParams();
        unset($parameters[self::PAGE_QUERY_PARAMETER]);
        return $parameters;
    }

    /**
     * @param int|null $readyCategorySeoMixId
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Model\Category\Category $category
     * @return \App\Model\CategorySeo\ReadyCategorySeoMix|null
     */
    private function findReadyCategorySeoMix(?int $readyCategorySeoMixId, Request $request, Category $category): ?ReadyCategorySeoMix
    {
        $readyCategorySeoMix = null;

        if ($readyCategorySeoMixId !== null) {
            $readyCategorySeoMix = $this->readyCategorySeoMixFacade->getById($readyCategorySeoMixId);
        } elseif ($request->isXmlHttpRequest()) {
            try {
                $readyCategorySeoMix = $this->readyCategorySeoMixFacade->getCategorySeoMixByRawQueryData(
                    $category->getId(),
                    $request->query->all()
                );
            } catch (UnableToFindReadyCategorySeoMixException $exception) {
                // It is okay, current url is common product_list without CategorySeoMix
            }
        }

        return $readyCategorySeoMix;
    }
}
