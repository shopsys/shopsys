<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Model\Product\Availability\ProductAvailabilityFacade;
use App\Model\Store\StoreFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;

class PersonalPickupController extends FrontBaseController
{
    public const SELECTED_PERSONAL_PICKUP_KEY = 'selectedPersonalPickup';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \App\Model\Store\StoreFacade
     */
    private StoreFacade $storeFacade;

    /**
     * @var \App\Model\Cart\CartFacade
     */
    private CartFacade $cartFacade;

    /**
     * @var \App\Model\Product\Availability\ProductAvailabilityFacade
     */
    private ProductAvailabilityFacade $productAvailabilityFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Store\StoreFacade $storeFacade
     * @param \App\Model\Cart\CartFacade $cartFacade
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     */
    public function __construct(
        Domain $domain,
        StoreFacade $storeFacade,
        CartFacade $cartFacade,
        ProductAvailabilityFacade $productAvailabilityFacade
    ) {
        $this->domain = $domain;
        $this->cartFacade = $cartFacade;
        $this->productAvailabilityFacade = $productAvailabilityFacade;
        $this->storeFacade = $storeFacade;
    }

    public function indexAction()
    {
        $domainId = $this->domain->getId();
        $quantifiedProducts = $this->cartFacade->getQuantifiedProductsOfCurrentCustomer();
        $stores = $this->storeFacade->getStoresEnabledOnDomainIndexedByStoreId($domainId);

        return $this->render('Front/Content/Order/Transport/personalPickup.html.twig', [
            'stores' => $stores,
            'storeDayAvailabilitiesByStoreId' => $this->productAvailabilityFacade->getStoreDayAvailabilitiesIndexedByStoreId(
                $domainId,
                $stores,
                $quantifiedProducts
            ),
        ]);
    }
}
