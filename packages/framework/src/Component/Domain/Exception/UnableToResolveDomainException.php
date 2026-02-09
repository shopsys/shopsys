<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain\Exception;

use Exception;

class UnableToResolveDomainException extends Exception
{
    public function __construct(string $url, ?Exception $previous = null)
    {
        $message = sprintf(
            'Unable to resolve domain for URL "%s". Check your configuration in "config/domains_urls.yaml".',
            $url,
        );

        parent::__construct($message, 0, $previous);
    }
}
