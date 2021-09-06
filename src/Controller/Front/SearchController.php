<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Model\Article\CombinedArticleElasticsearchFacade;
use App\Model\Product\Brand\BrandFacade;
use App\Model\Product\Filter\ProductFilterDataFactory;
use App\Model\Product\Listed\ListedProductViewElasticFacade;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends FrontBaseController
{
    protected const AUTOCOMPLETE_BRAND_LIMIT = 3;
    protected const AUTOCOMPLETE_CATEGORY_LIMIT = 3;
    protected const AUTOCOMPLETE_PRODUCT_LIMIT = 5;
    protected const AUTOCOMPLETE_ARTICLE_LIMIT = 3;

    /**
     * @var \App\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @var \App\Model\Product\Listed\ListedProductViewElasticFacade
     */
    private $listedProductViewFacade;

    /**
     * @var \App\Model\Product\Brand\BrandFacade
     */
    private $brandFacade;

    /**
     * @var \App\Model\Article\CombinedArticleElasticsearchFacade
     */
    private CombinedArticleElasticsearchFacade $elasticsearchArticleFacade;

    /**
     * @var \App\Model\Product\Filter\ProductFilterDataFactory
     */
    private ProductFilterDataFactory $productFilterDataFactory;

    /**
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\Product\Listed\ListedProductViewElasticFacade $listedProductViewFacade
     * @param \App\Model\Product\Brand\BrandFacade $brandFacade
     * @param \App\Model\Article\CombinedArticleElasticsearchFacade $elasticsearchArticleFacade
     * @param \App\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        ListedProductViewElasticFacade $listedProductViewFacade,
        BrandFacade $brandFacade,
        CombinedArticleElasticsearchFacade $elasticsearchArticleFacade,
        ProductFilterDataFactory $productFilterDataFactory
    ) {
        $this->categoryFacade = $categoryFacade;
        $this->listedProductViewFacade = $listedProductViewFacade;
        $this->brandFacade = $brandFacade;
        $this->elasticsearchArticleFacade = $elasticsearchArticleFacade;
        $this->productFilterDataFactory = $productFilterDataFactory;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function autocompleteAction(Request $request)
    {
        $searchText = trim($request->get('searchText'));
        $searchUrl = $this->generateUrl('front_product_search', [ProductController::SEARCH_TEXT_PARAMETER => $searchText]);

        $categoriesPaginationResult = $this->categoryFacade->getSearchAutocompleteCategories(
            $searchText,
            self::AUTOCOMPLETE_CATEGORY_LIMIT
        );

        $brandsPaginationResult = $this->brandFacade->getSearchAutocompleteBrands(
            $searchText,
            self::AUTOCOMPLETE_BRAND_LIMIT
        );

        $productFilterData = $this->productFilterDataFactory->create();
        $productFilterData->setSearchText($searchText);
        $productsPaginationResult = $this->listedProductViewFacade->getFilteredPaginatedForSearch(
            $searchText,
            $productFilterData,
            ProductListOrderingConfig::ORDER_BY_RELEVANCE,
            1,
            self::AUTOCOMPLETE_PRODUCT_LIMIT
        );

        $articlesPaginationResult = $this->elasticsearchArticleFacade->getSearchAutocompleteArticles(
            $searchText,
            self::AUTOCOMPLETE_ARTICLE_LIMIT
        );

        return $this->render('Front/Content/Search/autocomplete.html.twig', [
            'searchUrl' => $searchUrl,
            'brandsPaginationResult' => $brandsPaginationResult,
            'categoriesPaginationResult' => $categoriesPaginationResult,
            'productsPaginationResult' => $productsPaginationResult,
            'articlesPaginationResult' => $articlesPaginationResult,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function boxAction(Request $request)
    {
        $searchText = TransformString::replaceInvalidUtf8CharactersByQuestionMark(
            trim((string)$request->query->get(ProductController::SEARCH_TEXT_PARAMETER))
        );

        return $this->render('Front/Content/Search/searchBox.html.twig', [
            'searchText' => $searchText,
            'SEARCH_TEXT_PARAMETER' => ProductController::SEARCH_TEXT_PARAMETER,
        ]);
    }
}
