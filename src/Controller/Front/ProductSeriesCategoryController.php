<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Model\Product\Series\Category\ProductSeriesCategoryFacade;
use App\Model\Product\Series\ProductSeriesFacade;
use Symfony\Component\HttpFoundation\Response;

class ProductSeriesCategoryController extends FrontBaseController
{
    /**
     * @var \App\Model\Product\Series\Category\ProductSeriesCategoryFacade
     */
    private $productSeriesCategoryFacade;

    /**
     * @var \App\Model\Product\Series\ProductSeriesFacade
     */
    private $productSeriesFacade;

    /**
     * @param \App\Model\Product\Series\Category\ProductSeriesCategoryFacade $productSeriesCategoryFacade
     * @param \App\Model\Product\Series\ProductSeriesFacade $productSeriesFacade
     */
    public function __construct(
        ProductSeriesCategoryFacade $productSeriesCategoryFacade,
        ProductSeriesFacade $productSeriesFacade
    ) {
        $this->productSeriesCategoryFacade = $productSeriesCategoryFacade;
        $this->productSeriesFacade = $productSeriesFacade;
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailAction(int $id): Response
    {
        $productSeriesCategory = $this->productSeriesCategoryFacade->getById($id);
        $productSeries = $this->productSeriesFacade->getByProductSeriesCategoryForCurrentDomain($productSeriesCategory);

        return $this->render('Front/Content/ProductSeriesCategory/detail.html.twig', [
            'productSeriesCategory' => $productSeriesCategory,
            'productSeriesList' => $productSeries,
        ]);
    }
}
