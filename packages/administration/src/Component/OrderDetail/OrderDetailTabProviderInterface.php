<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\OrderDetail;

interface OrderDetailTabProviderInterface
{
    /**
     * @return iterable<\Shopsys\AdministrationBundle\Component\OrderDetail\OrderDetailTab>
     */
    public function getTabs(): iterable;
}
