<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Model\Product\Filter\ProductFilterData;
use App\Model\Product\Filter\ProductVariantFilterFacade;
use App\Model\Product\Listed\ListedProductViewElasticFacade;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends FrontBaseController
{
    protected const AUTOCOMPLETE_CATEGORY_LIMIT = 3;
    protected const AUTOCOMPLETE_PRODUCT_LIMIT = 5;

    /**
     * @var \App\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @var \App\Model\Product\Filter\ProductVariantFilterFacade
     */
    private $productVariantFilterFacade;

    /**
     * @var \App\Model\Product\Listed\ListedProductViewElasticFacade
     */
    private $listedProductViewFacade;

    /**
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\Product\Filter\ProductVariantFilterFacade $productVariantFilterFacade
     * @param \App\Model\Product\Listed\ListedProductViewElasticFacade $listedProductViewFacade
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        ProductVariantFilterFacade $productVariantFilterFacade,
        ListedProductViewElasticFacade $listedProductViewFacade
    ) {
        $this->categoryFacade = $categoryFacade;
        $this->productVariantFilterFacade = $productVariantFilterFacade;
        $this->listedProductViewFacade = $listedProductViewFacade;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function autocompleteAction(Request $request)
    {
        $searchText = $request->get('searchText');
        $searchUrl = $this->generateUrl('front_product_search', [ProductController::SEARCH_TEXT_PARAMETER => $searchText]);

        $categoriesPaginationResult = $this->categoryFacade
            ->getSearchAutocompleteCategories($searchText, self::AUTOCOMPLETE_CATEGORY_LIMIT);

        $productFilterData = new ProductFilterData();
        $productFilterData->setSearchText($searchText);
        $productsPaginationResult = $this->listedProductViewFacade->getFilteredPaginatedForSearch(
            $searchText,
            $productFilterData,
            ProductListOrderingConfig::ORDER_BY_RELEVANCE,
            1,
            self::AUTOCOMPLETE_PRODUCT_LIMIT
        );

        $this->productVariantFilterFacade->setupMostValuableVariantsInPaginationResultByProductFilterData($productsPaginationResult, $productFilterData);

        return $this->render('Front/Content/Search/autocomplete.html.twig', [
            'searchUrl' => $searchUrl,
            'categoriesPaginationResult' => $categoriesPaginationResult,
            'productsPaginationResult' => $productsPaginationResult,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function boxAction(Request $request)
    {
        $searchText = $request->query->get(ProductController::SEARCH_TEXT_PARAMETER);

        return $this->render('Front/Content/Search/searchBox.html.twig', [
            'searchText' => $searchText,
            'SEARCH_TEXT_PARAMETER' => ProductController::SEARCH_TEXT_PARAMETER,
        ]);
    }
}
