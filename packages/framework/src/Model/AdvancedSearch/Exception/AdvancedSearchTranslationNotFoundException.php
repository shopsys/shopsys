<?php

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception;

use Exception;

class AdvancedSearchTranslationNotFoundException extends Exception implements AdvancedSearchException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
