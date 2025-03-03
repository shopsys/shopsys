<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception;

use Exception;

class FriendlyUrlIsNotMultidomainException extends Exception
{
    /**
     * @param string $routeName
     */
    public function __construct(string $routeName)
    {
        parent::__construct('Route "' . $routeName . '" does not support creating URL for multiple domains.');
    }
}
