<?php

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Shopsys\FrameworkBundle\Model\Script\Script;
use Symfony\Component\Form\DataTransformerInterface;

class ScriptPlacementToBooleanTransformer implements DataTransformerInterface
{
    /**
     * @param string|null $scriptPlacement
     */
    public function transform($scriptPlacement): ?bool
    {
        if ($scriptPlacement === Script::PLACEMENT_ORDER_SENT_PAGE) {
            return true;
        } elseif ($scriptPlacement === Script::PLACEMENT_ALL_PAGES) {
            return false;
        }
    }

    /**
     * @param bool $scriptHasOrderPlacement
     */
    public function reverseTransform($scriptHasOrderPlacement): string
    {
        if (!is_bool($scriptHasOrderPlacement)) {
            $message = 'Expected boolean, got "' . gettype($scriptHasOrderPlacement) . '".';
            throw new \Symfony\Component\Form\Exception\TransformationFailedException($message);
        } elseif ($scriptHasOrderPlacement) {
            return Script::PLACEMENT_ORDER_SENT_PAGE;
        }

        return Script::PLACEMENT_ALL_PAGES;
    }
}
