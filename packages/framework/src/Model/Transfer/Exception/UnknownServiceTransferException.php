<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer\Exception;

use Exception;

class UnknownServiceTransferException extends Exception
{
    /**
     * @param string $serviceTransferIdentifier
     */
    public function __construct(string $serviceTransferIdentifier)
    {
        parent::__construct(sprintf('Unknown service transfer identifier %s.', $serviceTransferIdentifier));
    }
}
