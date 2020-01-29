<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Model\Product\Series\ProductSeriesFacadeInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\HttpFoundation\Response;

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

    /**
     * @param \App\Model\Product\Series\ProductSeriesFacadeInterface $productSeriesFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(ProductSeriesFacadeInterface $productSeriesFacade, Domain $domain)
    {
        $this->productSeriesFacade = $productSeriesFacade;
        $this->domain = $domain;
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailAction(int $id): Response
    {
        $productSeries = $this->productSeriesFacade->getVisibleProductSeriesByIdAndDomainId($id, $this->domain->getId());

        return $this->render('Front/Content/ProductSeries/detail.html.twig', [
            'productSeries' => $productSeries,
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        $productSeries = $this->productSeriesFacade->getAllVisibleProductSeriesByDomainId($this->domain->getId());

        return $this->render('Front/Content/ProductSeries/list.html.twig', [
            'productSeries' => $productSeries,
        ]);
    }
}
