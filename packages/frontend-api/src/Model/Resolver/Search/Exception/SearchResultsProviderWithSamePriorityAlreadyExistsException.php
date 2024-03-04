<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Search\Exception;

use Exception;

class SearchResultsProviderWithSamePriorityAlreadyExistsException extends Exception
{
    /**
     * @param string $serviceId
     * @param int $priority
     */
    public function __construct(string $serviceId, int $priority)
    {
        parent::__construct(
            sprintf('Cannot register SearchResultsProviderInterface "%s" with priority "%d" as provider with same priority already exists.', $serviceId, $priority),
        );
    }
}
