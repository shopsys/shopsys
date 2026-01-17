<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Exception;

use Exception;
use Shopsys\FrameworkBundle\Component\Utils\Debug;

class InvalidGridLimitValueException extends Exception
{
    protected mixed $limit;

    /**
     * @param mixed $limit
     */
    public function __construct($limit, ?Exception $previous = null)
    {
        parent::__construct('Administrator grid limit value ' . Debug::export($limit) . ' is invalid', 0, $previous);
    }

    /**
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }
}
