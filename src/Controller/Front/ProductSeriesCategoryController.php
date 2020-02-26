<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Model\Product\Series\Category\ProductSeriesCategoryFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\HttpFoundation\Response;

class ProductSeriesCategoryController extends FrontBaseController
{


    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;
    /**
     * @var \App\Model\Product\Series\Category\ProductSeriesCategoryFacade
     */
    private $productSeriesCategoryFacade;

    /**
     * @param \App\Model\Product\Series\Category\ProductSeriesCategoryFacade $productSeriesCategoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(ProductSeriesCategoryFacade $productSeriesCategoryFacade, Domain $domain)
    {
        $this->domain = $domain;
        $this->productSeriesCategoryFacade = $productSeriesCategoryFacade;
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailAction(int $id): Response
    {
        $productSeries = $this->productSeriesCategoryFacade->getById($id);

        return $this->render('Front/Content/ProductSeriesCategory/detail.html.twig', [
            'productSeriesCategory' => $productSeries,
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(int $productSeriesId): Response
    {
        $productSeries = $this->productSeriesCategoryFacade->getProductSeriesCategoriesByProductSeriesId($productSeriesId);

        return $this->render('Front/Content/ProductSeriesCategory/list.html.twig', [
            'productSeriesCategories' => $productSeries,
        ]);
    }
}
