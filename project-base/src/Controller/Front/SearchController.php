<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Shopsys\FrameworkBundle\Component\String\TransformString;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends FrontBaseController
{
    protected const AUTOCOMPLETE_CATEGORY_LIMIT = 3;
    protected const AUTOCOMPLETE_PRODUCT_LIMIT = 5;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade
     */
    public function __construct(
        private readonly CategoryFacade $categoryFacade,
        private readonly ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function autocompleteAction(Request $request)
    {
        $searchText = trim($request->get('searchText'));
        $searchUrl = $this->generateUrl(
            'front_product_search',
            [ProductController::SEARCH_TEXT_PARAMETER => $searchText],
        );

        $categoriesPaginationResult = $this->categoryFacade
            ->getSearchAutocompleteCategories($searchText, self::AUTOCOMPLETE_CATEGORY_LIMIT);

        $productsPaginationResult = $this->productOnCurrentDomainFacade
            ->getSearchAutocompleteProducts($searchText, self::AUTOCOMPLETE_PRODUCT_LIMIT);

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
        $searchText = TransformString::replaceInvalidUtf8CharactersByQuestionMark(
            trim((string)$request->query->get(ProductController::SEARCH_TEXT_PARAMETER)),
        );

        return $this->render('Front/Content/Search/searchBox.html.twig', [
            'searchText' => $searchText,
            'SEARCH_TEXT_PARAMETER' => ProductController::SEARCH_TEXT_PARAMETER,
        ]);
    }
}
