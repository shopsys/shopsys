<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed\Exception;

use Exception;

class FeedCurrencyNotEnabledOnDomainException extends Exception
{
    public function __construct(string $feedName, string $currencyCode, int $domainId, ?Exception $previous = null)
    {
        parent::__construct(
            sprintf('Feed "%s" is configured to be generated in currency "%s" that is not enabled on domain with ID %d.', $feedName, $currencyCode, $domainId),
            0,
            $previous,
        );
    }
}
