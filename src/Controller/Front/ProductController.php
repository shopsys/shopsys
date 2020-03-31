<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Form\Front\Product\ProductFilterFormType;
use App\Model\Category\CategoryFacade;
use App\Model\Category\CategoryProductSeries\CategoryProductSeriesFacade;
use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use App\Model\Product\Availability\ProductAvailabilityFacade;
use App\Model\Product\Listed\ListedProductViewElasticFacade;
use App\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Module\ModuleFacade;
use Shopsys\FrameworkBundle\Model\Module\ModuleList;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForBrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForListFacade;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForSearchFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Shopsys\FrameworkBundle\Twig\RequestExtension;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends FrontBaseController
{
    public const SEARCH_TEXT_PARAMETER = 'q';
    public const PAGE_QUERY_PARAMETER = 'page';
    public const PRODUCTS_PER_PAGE = 12;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory
     */
    private $productFilterConfigFactory;

    /**
     * @var \App\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
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
     * @var \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForBrandFacade
     */
    private $productListOrderingModeForBrandFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForSearchFacade
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
     * @param \Shopsys\FrameworkBundle\Twig\RequestExtension $requestExtension
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory $productFilterConfigFactory
     * @param \App\Model\Product\Listing\ProductListOrderingModeForListFacade $productListOrderingModeForListFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForBrandFacade $productListOrderingModeForBrandFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForSearchFacade $productListOrderingModeForSearchFacade
     * @param \Shopsys\FrameworkBundle\Model\Module\ModuleFacade $moduleFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     * @param \App\Model\Product\Listed\ListedProductViewElasticFacade $listedProductViewFacade
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \App\Model\Category\CategoryProductSeries\CategoryProductSeriesFacade $categoryProductSeriesFacade

     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     * @param \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade $seoSettingFacade
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
        SeoSettingFacade $seoSettingFacade
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

        /** @var \App\Model\Product\Product $product */
        $product = $this->productOnCurrentDomainFacade->getVisibleProductById($id);

        if ($product->isVariant()) {
            return $this->redirectToRoute('front_product_detail', ['id' => $product->getMainVariant()->getId()]);
        }

        $accessories = $this->listedProductViewFacade->getAllAccessories($product->getId());
        $variants = $this->productOnCurrentDomainFacade->getVariantsForProduct($product);
        $productMainCategory = $this->categoryFacade->getProductMainCategoryByDomainId($product, $this->domain->getId());
        $categoryList = $this->categoryFacade->getAllProductCategoriesByProductAndDomainId($product, $this->domain->getId());
        $productAvailabilityInformation = $this->productAvailabilityFacade->getProductAvailabilityInformationByDomainId($product, $this->domain->getId());
        $productStocksAvailabilitiesInformation = $this->productAvailabilityFacade->getProductStocksAvailabilitiesInformationByDomainId($product, $this->domain->getId());
        $downloadFiles = $this->productFacade->getDownloadFilesForProductByDomain($product, $this->domain);

        $paginatedSimilarProducts = $this->listedProductViewFacade->getSimilarPaginatedProductsFormProductInCategory(
            $product,
            $this->domain->getId(),
            ProductListOrderingConfig::ORDER_BY_PRIORITY,
            $page,
            self::PRODUCTS_PER_PAGE
        );

        return $this->render('Front/Content/Product/detail.html.twig', [
            'product' => $product,
            'accessories' => $accessories,
            'variants' => $variants,
            'productMainCategory' => $productMainCategory,
            'categoryList' => $categoryList,
            'domain' => $this->domain,
            'productAvailabilityInformation' => $productAvailabilityInformation,
            'productStocksAvailabilitiesInformation' => $productStocksAvailabilitiesInformation,
            'downloadFiles' => $downloadFiles,
            'paginatedSimilarProducts' => $paginatedSimilarProducts,
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

        $requestPage = $request->get(self::PAGE_QUERY_PARAMETER);
        if (!$this->isRequestPageValid($requestPage)) {
            return $this->redirectToRoute('front_product_list', $this->getRequestParametersWithoutPage());
        }
        $page = $requestPage === null ? 1 : (int)$requestPage;

        $orderingModeId = $this->productListOrderingModeForListFacade->getOrderingModeIdFromRequest(
            $request
        );

        $productFilterData = new ProductFilterData();
        $productFilterData->inStock = true;

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

        $productFilterCountData = null;
        if ($this->moduleFacade->isEnabled(ModuleList::PRODUCT_FILTER_COUNTS)) {
            $productFilterCountData = $this->productOnCurrentDomainFacade->getProductFilterCountDataInCategory(
                $id,
                $productFilterConfig,
                $productFilterData
            );
        }

        $viewParameters = [
            'paginationResult' => $paginationResult,
            'productFilterCountData' => $productFilterCountData,
            'category' => $category,
            'filterForm' => $filterForm->createView(),
            'filterFormSubmitted' => $filterForm->isSubmitted(),
            'visibleChildren' => $this->categoryFacade->getAllVisibleChildrenByCategoryAndDomainId($category, $this->domain->getId()),
            'priceRange' => $productFilterConfig->getPriceRange(),
            'categoryProductSeries' => $this->categoryProductSeriesFacade->getAllCategoryProductSeriesByCategory($category),
        ];

        $viewParameters = array_merge($viewParameters, $this->getAdditionalSeoViewParameters($category, $readyCategorySeoMixId));

        if ($request->isXmlHttpRequest()) {
            return $this->render('Front/Content/Product/ajaxList.html.twig', $viewParameters);
        } else {
            return $this->render('Front/Content/Product/list.html.twig', $viewParameters);
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

        $orderingModeId = $this->productListOrderingModeForBrandFacade->getOrderingModeIdFromRequest(
            $request
        );

        $paginationResult = $this->listedProductViewFacade->getPaginatedForBrand(
            $id,
            $orderingModeId,
            $page,
            self::PRODUCTS_PER_PAGE
        );

        $brand = $this->brandFacade->getById($id);

        $viewParameters = [
            'paginationResult' => $paginationResult,
            'brand' => $brand,
        ];

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

        $productFilterCountData = null;
        if ($this->moduleFacade->isEnabled(ModuleList::PRODUCT_FILTER_COUNTS)) {
            $productFilterCountData = $this->productOnCurrentDomainFacade->getProductFilterCountDataForSearch(
                $searchText,
                $productFilterConfig,
                $productFilterData
            );
        }

        $viewParameters = [
            'paginationResult' => $paginationResult,
            'productFilterCountData' => $productFilterCountData,
            'filterForm' => $filterForm->createView(),
            'filterFormSubmitted' => $filterForm->isSubmitted(),
            'searchText' => $searchText,
            'SEARCH_TEXT_PARAMETER' => self::SEARCH_TEXT_PARAMETER,
            'priceRange' => $productFilterConfig->getPriceRange(),
        ];

        if ($request->isXmlHttpRequest()) {
            return $this->render('Front/Content/Product/ajaxSearch.html.twig', $viewParameters);
        } else {
            $viewParameters['foundCategories'] = $this->searchCategories($searchText);
            return $this->render('Front/Content/Product/search.html.twig', $viewParameters);
        }
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param int|null $readyCategorySeoMixId
     * @return string[]
     */
    private function getAdditionalSeoViewParameters(Category $category, ?int $readyCategorySeoMixId = null): array
    {
        $domainId = $this->domain->getId();

        if ($readyCategorySeoMixId === null) {
            $seoH1 = $category->getSeoH1($domainId);
            $description = $category->getDescription($domainId);
            $seoTitle = $category->getSeoTitle($domainId);
            $seoMetaDescription = $category->getSeoMetaDescription($domainId);
        } else {
            $readyCategorySeoMix = $this->readyCategorySeoMixFacade->getById($readyCategorySeoMixId);

            $seoH1 = $readyCategorySeoMix->getH1();
            $description = $readyCategorySeoMix->getDescription() ?? $category->getDescription($domainId);
            $seoTitle = $readyCategorySeoMix->getTitle() ?? $category->getSeoTitle($domainId);
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
            'seoH1' => $seoH1,
            'description' => $description,
            'seoTitle' => $seoTitle,
            'seoMetaDescription' => $seoMetaDescription,
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
     */
    public function selectOrderingModeForListAction(Request $request)
    {
        $productListOrderingConfig = $this->productListOrderingModeForListFacade->getProductListOrderingConfig();

        $orderingModeId = $this->productListOrderingModeForListFacade->getOrderingModeIdFromRequest(
            $request
        );

        return $this->render('Front/Content/Product/orderingSetting.html.twig', [
            'orderingModesNames' => $productListOrderingConfig->getSupportedOrderingModesNamesIndexedById(),
            'activeOrderingModeId' => $orderingModeId,
            'cookieName' => $productListOrderingConfig->getCookieName(),
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
}
