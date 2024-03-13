<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Component\LuigisBox\Exception;

use Exception;

class LuigisBoxActionNotRecognizedException extends Exception
{
    /**
     * @param string $action
     */
    public function __construct(string $action)
    {
        parent::__construct(sprintf('Provided action "%s" is not recognized Luigi\'s Box action.', $action));
    }
}
