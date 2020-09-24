<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Component\Domain\Domain;
use App\Model\Stock\StockFacade;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class StoreController extends FrontBaseController
{
    /**
     * @var \App\Model\Stock\StockFacade
     */
    private StockFacade $stockFacade;

    /**
     * @var \App\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var string
     */
    private $googleMapApiKey;

    /**
     * StoreController constructor.
     * @param \App\Model\Stock\StockFacade $stockFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(StockFacade $stockFacade, Domain $domain, ContainerInterface $container)
    {
        $this->stockFacade = $stockFacade;
        $this->domain = $domain;
        $this->googleMapApiKey = $container->getParameter('google_map_api_key');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(): Response
    {
        return $this->render('Front/Content/Store/store.html.twig', [
            'stores' => $this->stockFacade->getStocksWithoutCentralByDomainIdIndexedByStockId($this->domain->getId()),
            'googleMapApiKey' => $this->googleMapApiKey,
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
            'googleMapApiKey' => $this->googleMapApiKey,
        ]);
    }
}
