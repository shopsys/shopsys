<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\Exception;

use LogicException;

class MissingCustomerUserLoginTypeException extends LogicException
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct('No customer user login type found for the current customer.');
    }
}
