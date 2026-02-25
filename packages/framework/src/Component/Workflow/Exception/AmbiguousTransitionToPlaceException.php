<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Workflow\Exception;

use LogicException;

class AmbiguousTransitionToPlaceException extends LogicException
{
    /**
     * @param array<int, string> $currentPlaces
     * @param array<int, string> $transitionNames
     */
    public function __construct(
        string $workflowName,
        array $currentPlaces,
        string $targetPlace,
        array $transitionNames,
    ) {
        parent::__construct(sprintf(
            'Workflow "%s" has multiple transitions from current marking [%s] to place "%s": %s',
            $workflowName,
            implode(', ', $currentPlaces),
            $targetPlace,
            implode(', ', $transitionNames),
        ));
    }
}
