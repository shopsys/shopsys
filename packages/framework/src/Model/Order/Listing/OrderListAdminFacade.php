<?php

namespace Shopsys\FrameworkBundle\Model\Order\Listing;

use Shopsys\FrameworkBundle\Model\Localization\Localization;

class OrderListAdminFacade
{
    protected OrderListAdminRepository $orderListAdminRepository;

    protected Localization $localization;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Listing\OrderListAdminRepository $orderListAdminRepository
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        OrderListAdminRepository $orderListAdminRepository,
        Localization $localization
    ) {
        $this->orderListAdminRepository = $orderListAdminRepository;
        $this->localization = $localization;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrderListQueryBuilder()
    {
        return $this->orderListAdminRepository->getOrderListQueryBuilder($this->localization->getAdminLocale());
    }
}
