<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Exception;

use Exception;

class RememberGridLimitException extends Exception
{
    public function __construct(protected string $gridId, ?Exception $previous = null)
    {
        parent::__construct('Grid \'' . $this->gridId . ' \' does not allow paging', 0, $previous);
    }

    public function getGridId(): string
    {
        return $this->gridId;
    }
}
