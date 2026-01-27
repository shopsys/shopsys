<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\AdvancedSearch;

use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchConfig;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderCityFilter;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderCreateDateFilter;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderCustomerIdFilter;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderEmailFilter;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderLastNameFilter;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderNameFilter;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderNumberFilter;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderPhoneNumberFilter;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderPriceFilterWithVatFilter;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderProductFilter;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderStatusFilter;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderStreetFilter;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderWithdrawalRequestFilter;

class OrderAdvancedSearchConfig extends AdvancedSearchConfig
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderNumberFilter $orderNumberFilter
     * @param \Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderCreateDateFilter $orderCreateDateFilter
     * @param \Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderPriceFilterWithVatFilter $orderPriceFilterWithVatFilter
     * @param \Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderStatusFilter $orderStatusFilter
     * @param \Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderProductFilter $orderProductFilter
     * @param \Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderPhoneNumberFilter $orderPhoneNumberFilter
     * @param \Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderStreetFilter $orderStreetFilter
     * @param \Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderNameFilter $orderNameFilter
     * @param \Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderLastNameFilter $orderLastNameFilter
     * @param \Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderEmailFilter $orderEmailFilter
     * @param \Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderCityFilter $orderCityFilter
     * @param \Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderCustomerIdFilter $orderCustomerIdFilter
     * @param \Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderWithdrawalRequestFilter $orderWithdrawalFilter
     * @throws \Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchFilterAlreadyExistsException
     */
    public function __construct(
        OrderNumberFilter $orderNumberFilter,
        OrderCreateDateFilter $orderCreateDateFilter,
        OrderPriceFilterWithVatFilter $orderPriceFilterWithVatFilter,
        OrderStatusFilter $orderStatusFilter,
        OrderProductFilter $orderProductFilter,
        OrderPhoneNumberFilter $orderPhoneNumberFilter,
        OrderStreetFilter $orderStreetFilter,
        OrderNameFilter $orderNameFilter,
        OrderLastNameFilter $orderLastNameFilter,
        OrderEmailFilter $orderEmailFilter,
        OrderCityFilter $orderCityFilter,
        OrderCustomerIdFilter $orderCustomerIdFilter,
        OrderWithdrawalRequestFilter $orderWithdrawalFilter,
    ) {
        parent::__construct();

        $this->registerFilter($orderPriceFilterWithVatFilter);
        $this->registerFilter($orderNumberFilter);
        $this->registerFilter($orderCreateDateFilter);
        $this->registerFilter($orderStatusFilter);
        $this->registerFilter($orderProductFilter);
        $this->registerFilter($orderNameFilter);
        $this->registerFilter($orderLastNameFilter);
        $this->registerFilter($orderEmailFilter);
        $this->registerFilter($orderPhoneNumberFilter);
        $this->registerFilter($orderStreetFilter);
        $this->registerFilter($orderCityFilter);
        $this->registerFilter($orderCustomerIdFilter);
        $this->registerFilter($orderWithdrawalFilter);
    }
}
