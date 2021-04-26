<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Model\Stock\StockFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\HttpFoundation\Response;

class StoreController extends FrontBaseController
{
    /**
     * @var \App\Model\Stock\StockFacade
     */
    private StockFacade $stockFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * StoreController constructor.
     *
     * @param \App\Model\Stock\StockFacade $stockFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(StockFacade $stockFacade, Domain $domain)
    {
        $this->stockFacade = $stockFacade;
        $this->domain = $domain;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(): Response
    {
        return $this->render('Front/Content/Store/store.html.twig', [
            'stores' => $this->stockFacade->getStocksEnabledOnDomainIndexedByStockId($this->domain->getId()),
        ]);
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailAction(int $id): Response
    {
        $store = $this->stockFacade->getById($id);

        return $this->render('Front/Content/Store/detail.html.twig', [
            'store' => $store,
        ]);
    }
}
