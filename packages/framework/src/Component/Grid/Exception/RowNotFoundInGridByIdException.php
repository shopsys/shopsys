<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid\Exception;

use Exception;

class RowNotFoundInGridByIdException extends Exception implements GridException
{
    /**
     * @param string $message
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
