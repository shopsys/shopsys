<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Customer\Exception;

use Shopsys\ConvertimBundle\Model\Convertim\Exception\ConvertimException;

class CustomerDetailsNotFoundException extends ConvertimException
{
    /**
     * @param string[] $context
     * @param \Exception|null $previous
     */
    public function __construct(array $context, $previous = null)
    {
        parent::__construct('Customer details not found', $context, 404, $previous);
    }
}
