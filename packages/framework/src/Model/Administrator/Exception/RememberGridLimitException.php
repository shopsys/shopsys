<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Exception;

use Exception;

class RememberGridLimitException extends Exception
{
    protected string $gridId;

    /**
     * @param string $gridId
     */
    public function __construct($gridId, ?Exception $previous = null)
    {
        $this->gridId = $gridId;

        parent::__construct('Grid \'' . $this->gridId . ' \' does not allow paging', 0, $previous);
    }

    /**
     * @return string
     */
    public function getGridId()
    {
        return $this->gridId;
    }
}
