<?php

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Shopsys\FrameworkBundle\Model\Script\Script;
use Symfony\Component\Form\DataTransformerInterface;

class ScriptPlacementToBooleanTransformer implements DataTransformerInterface
{
    /**
     * @param string|null $scriptPlacement
     * @return bool|null
     */
    public function transform($scriptPlacement)
    {
        return $scriptPlacement === Script::PLACEMENT_ORDER_SENT_PAGE;
    }

    /**
     * @param bool $scriptHasOrderPlacement
     * @return string
     */
    public function reverseTransform($scriptHasOrderPlacement)
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
