<?php

namespace Tests\FrameworkBundle\Unit\Model\Customer;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Customer\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Customer\Exception\EmptyCustomerIdentifierException;

class CustomerUserIdentifierTest extends TestCase
{
    public function testCannotCreateIdentifierForEmptyCartIdentifierAndNullUser()
    {
        $cartIdentifier = '';
        $user = null;

        $this->expectException(EmptyCustomerIdentifierException::class);
        new CustomerUserIdentifier($cartIdentifier, $user);
    }
}
