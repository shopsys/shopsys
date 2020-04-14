<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Model\Stock\StockFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;

class PersonalPickupController extends FrontBaseController
{
    public const SELECTED_PERSONAL_PICKUP_KEY = 'selectedPersonalPickup';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Model\Stock\StockFacade
     */
    private $stockFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\CartFacade
     */
    private $cartFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Stock\StockFacade $stockFacade
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     */
    public function __construct(
        Domain $domain,
        StockFacade $stockFacade,
        CartFacade $cartFacade
    ) {
        $this->domain = $domain;
        $this->stockFacade = $stockFacade;
        $this->cartFacade = $cartFacade;
    }

    public function indexAction()
    {
        $domainId = $this->domain->getId();
        $quantifiedProducts = $this->cartFacade->getQuantifiedProductsOfCurrentCustomer();
        $stocks = $this->stockFacade->getStocksWithoutCentralByDomainIdIndexedByStockId($domainId);

        return $this->render('Front/Content/Order/Transport/personalPickup.html.twig', [
            'stocks' => $stocks,
            'stockDayAvailabilitiesByStockId' => $this->stockFacade->getStockDayAvailabilitiesIndexedByStockId(
                $domainId,
                $stocks,
                $quantifiedProducts
            ),
        ]);
    }
}
