<?php

namespace Shopsys\FrameworkBundle\Model\Order\Listing;

use Shopsys\FrameworkBundle\Model\Localization\Localization;

class OrderListAdminFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Listing\OrderListAdminRepository
     */
    protected $orderListAdminRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    protected $localization;

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
