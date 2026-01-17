<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid\InlineEdit\Exception;

use Exception;

class InvalidServiceException extends Exception
{
    /**
     * @param string $serviceName
     */
    public function __construct($serviceName, ?Exception $previous = null)
    {
        $message = 'Service with name "' . $serviceName . '" does not exist or not implement necessary interface.';

        parent::__construct($message, 0, $previous);
    }
}
