<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\SalesRepresentative;

interface SalesRepresentativeDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeData
     */
    public function create(): SalesRepresentativeData;

    /**
     * @param \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentative $salesRepresentative
     * @return \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeData
     */
    public function createFromSalesRepresentative(SalesRepresentative $salesRepresentative): SalesRepresentativeData;
}
