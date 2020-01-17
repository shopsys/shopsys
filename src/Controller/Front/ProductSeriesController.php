<?php

declare(strict_types=1);


namespace App\Controller\Front;


use App\Model\Product\Series\ProductSeriesFacadeInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class ProductSeriesController extends FrontBaseController
{

    /**
     * @var \App\Model\Product\Series\ProductSeriesFacadeInterface
     */
    private $productSeriesFacade;
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    public function __construct(ProductSeriesFacadeInterface $productSeriesFacade, Domain $domain)
    {
        $this->productSeriesFacade = $productSeriesFacade;
        $this->domain = $domain;
    }

    /**
     * @param int $id
     */
    public function detailAction(int $id)
    {
        $productSeries = $this->productSeriesFacade->getVisibleProductSeriesById($id, $this->domain->getId());

        d($productSeries->getName());

        return $this->render('Front/Content/ProductSeries/detail.html.twig', [
            'productSeries' => $productSeries,
        ]);
    }
}