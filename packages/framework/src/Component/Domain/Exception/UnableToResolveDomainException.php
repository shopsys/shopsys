<?php

namespace Shopsys\FrameworkBundle\Component\Domain\Exception;

use Exception;

class UnableToResolveDomainException extends Exception implements DomainException
{
    public function __construct(string $url, ?\Exception $previous = null)
    {
        $message = sprintf(
            'Unable to resolve domain for URL "%s". Check your configuration in "app/config/domains_urls.yml".',
            $url
        );

        parent::__construct($message, 0, $previous);
    }
}
