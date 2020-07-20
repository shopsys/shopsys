<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Model\Product\Availability\ProductAvailabilityFacade;
use App\Model\Stock\StockFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;

class PersonalPickupController extends FrontBaseController
{
    public const SELECTED_PERSONAL_PICKUP_KEY = 'selectedPersonalPickup';

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Model\Stock\StockFacade
     */
    private $stockFacade;

    /**
     * @var \App\Model\Cart\CartFacade
     */
    private $cartFacade;

    /**
     * @var \App\Model\Product\Availability\ProductAvailabilityFacade
     */
    private $productAvailabilityFacade;

    /**
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Model\Stock\StockFacade $stockFacade
     * @param \App\Model\Cart\CartFacade $cartFacade
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     */
    public function __construct(
        Domain $domain,
        StockFacade $stockFacade,
        CartFacade $cartFacade,
        ProductAvailabilityFacade $productAvailabilityFacade
    ) {
        $this->domain = $domain;
        $this->stockFacade = $stockFacade;
        $this->cartFacade = $cartFacade;
        $this->productAvailabilityFacade = $productAvailabilityFacade;
    }

    public function indexAction()
    {
        $domainId = $this->domain->getId();
        $quantifiedProducts = $this->cartFacade->getQuantifiedProductsOfCurrentCustomer();
        $stocks = $this->stockFacade->getStocksWithoutCentralByDomainIdIndexedByStockId($domainId);

        return $this->render('Front/Content/Order/Transport/personalPickup.html.twig', [
            'stocks' => $stocks,
            'stockDayAvailabilitiesByStockId' => $this->productAvailabilityFacade->getStockDayAvailabilitiesIndexedByStockId(
                $domainId,
                $stocks,
                $quantifiedProducts
            ),
        ]);
    }
}
