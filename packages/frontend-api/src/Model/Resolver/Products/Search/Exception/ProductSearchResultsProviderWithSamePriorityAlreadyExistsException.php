<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products\Search\Exception;

use Exception;

class ProductSearchResultsProviderWithSamePriorityAlreadyExistsException extends Exception
{
    /**
     * @param string $serviceId
     * @param int $priority
     */
    public function __construct(string $serviceId, int $priority)
    {
        parent::__construct(
            sprintf('Cannot register ProductSearchResultsProvider "%s" with priority "%d" as provider with same priority already exists.', $serviceId, $priority),
        );
    }
}
