<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Workflow;

use Shopsys\FrameworkBundle\Component\Workflow\Exception\AmbiguousTransitionToPlaceException;
use Shopsys\FrameworkBundle\Component\Workflow\Exception\TransitionToPlaceNotFoundException;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\TransitionBlocker;
use Symfony\Component\Workflow\TransitionBlockerList;
use Symfony\Component\Workflow\WorkflowInterface;

class TransitionNameByTargetPlaceResolver
{
    /**
     * Resolves a transition name for a state machine when the caller knows the desired target place.
     */
    public function getTransitionNameForTargetPlace(
        WorkflowInterface $workflow,
        object $subject,
        string $targetPlace,
    ): string {
        $currentPlaces = array_keys($workflow->getMarking($subject)->getPlaces());
        $matchingTransitions = [];
        $seenTransitionNames = [];

        foreach ($this->getTransitionsToTargetPlace($workflow, $targetPlace) as $transition) {
            if (isset($seenTransitionNames[$transition->getName()])) {
                continue;
            }

            $transitionBlockerList = $workflow->buildTransitionBlockerList($subject, $transition->getName());

            if (!$this->isReachableFromCurrentMarking($transitionBlockerList)) {
                continue;
            }

            $seenTransitionNames[$transition->getName()] = true;
            $matchingTransitions[] = $transition;
        }

        if (count($matchingTransitions) === 0) {
            throw new TransitionToPlaceNotFoundException($workflow->getName(), $currentPlaces, $targetPlace);
        }

        if (count($matchingTransitions) > 1) {
            throw new AmbiguousTransitionToPlaceException(
                $workflow->getName(),
                $currentPlaces,
                $targetPlace,
                array_map(static fn (Transition $transition): string => $transition->getName(), $matchingTransitions),
            );
        }

        return $matchingTransitions[0]->getName();
    }

    /**
     * @return array<int, \Symfony\Component\Workflow\Transition>
     */
    protected function getTransitionsToTargetPlace(WorkflowInterface $workflow, string $targetPlace): array
    {
        return array_values(array_filter(
            $workflow->getDefinition()->getTransitions(),
            static fn (Transition $transition): bool => $transition->getTos() === [$targetPlace],
        ));
    }

    protected function isReachableFromCurrentMarking(TransitionBlockerList $transitionBlockerList): bool
    {
        // Guard-blocked transitions are still valid matches for the current marking.
        // The caller resolves the name here and lets Workflow::apply() expose blocker messages.
        return $transitionBlockerList->isEmpty()
            || !$transitionBlockerList->has(TransitionBlocker::BLOCKED_BY_MARKING);
    }
}
