<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\Exception;

use Exception;

class BillingAddressCompanyNumberIsNotUniqueException extends Exception implements BillingAddressException
{
}
