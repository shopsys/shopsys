<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

interface CustomerFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    public function create(): Customer;
}
