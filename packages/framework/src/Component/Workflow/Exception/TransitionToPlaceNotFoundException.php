<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Workflow\Exception;

use LogicException;

class TransitionToPlaceNotFoundException extends LogicException
{
    /**
     * @param array<int, string> $currentPlaces
     */
    public function __construct(string $workflowName, array $currentPlaces, string $targetPlace)
    {
        parent::__construct(sprintf(
            'Workflow "%s" has no transition from current marking [%s] to place "%s".',
            $workflowName,
            implode(', ', $currentPlaces),
            $targetPlace,
        ));
    }
}
