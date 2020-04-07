<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Model\Stock\StockFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

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
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Stock\StockFacade $stockFacade
     */
    public function __construct(
        Domain $domain,
        StockFacade $stockFacade
    ) {
        $this->domain = $domain;
        $this->stockFacade = $stockFacade;
    }

    public function indexAction()
    {
        $domainId = $this->domain->getId();
        return $this->render('Front/Content/Order/Transport/personalPickup.html.twig', [
            'stocks' => $this->stockFacade->getStocksWithoutCentralByDomainIdIndexedByStockId($domainId),
        ]);
    }
}
