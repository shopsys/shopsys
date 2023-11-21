<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed\Exception;

use Exception;

class FeedNotFoundException extends Exception implements FeedException
{
    /**
     * @param string $name
     * @param int|null $domainId
     * @param \Exception|null $previous
     */
    public function __construct(
        string $name,
        ?int $domainId = null,
        ?Exception $previous = null,
    ) {
        $message = sprintf(
            'Feed with name "%s"%s not found.',
            $name,
            $domainId !== null ? sprintf(' and domain ID %d', $domainId) : '',
        );

        parent::__construct($message, 0, $previous);
    }
}
