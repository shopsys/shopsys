<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Listing;

use Shopsys\FrameworkBundle\Model\Localization\Localization;

class OrderListAdminFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Listing\OrderListAdminRepository $orderListAdminRepository
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        protected readonly OrderListAdminRepository $orderListAdminRepository,
        protected readonly Localization $localization,
    ) {
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrderListQueryBuilder()
    {
        return $this->orderListAdminRepository->getOrderListQueryBuilder($this->localization->getAdminLocale());
    }
}
