<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper;

use Exception;

class MethodNotFoundException extends Exception
{
    /**
     * @param string $fieldName
     * @param object $mapper
     * @param \Exception|null $previous
     */
    public function __construct(string $fieldName, object $mapper, ?Exception $previous = null)
    {
        $message = sprintf('Method "%s" not found in class "%s"', $fieldName, get_class($mapper));

        parent::__construct($message, 0, $previous);
    }
}
