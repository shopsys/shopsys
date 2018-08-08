<?php

namespace Shopsys\FrameworkBundle\Model\Administrator\Exception;

use Exception;

class RememberGridLimitException extends Exception implements AdministratorException
{
    /**
     * @var string
     */
    private $gridId;

    public function __construct(string $gridId, Exception $previous = null)
    {
        $this->gridId = $gridId;
        parent::__construct('Grid \'' . $this->gridId . ' \' does not allow paging', 0, $previous);
    }

    public function getGridId(): string
    {
        return $this->gridId;
    }
}
