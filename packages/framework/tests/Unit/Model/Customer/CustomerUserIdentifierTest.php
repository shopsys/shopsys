<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Customer;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Customer\Exception\EmptyCustomerUserIdentifierException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;

class CustomerUserIdentifierTest extends TestCase
{
    public function testCannotCreateIdentifierForEmptyCartIdentifierAndNullUser()
    {
        $cartIdentifier = '';
        $customerUser = null;

        $this->expectException(EmptyCustomerUserIdentifierException::class);
        new CustomerUserIdentifier($cartIdentifier, $customerUser);
    }
}
