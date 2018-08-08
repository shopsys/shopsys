<?php

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Symfony\Component\Form\DataTransformerInterface;

class InverseTransformer implements DataTransformerInterface
{
    /**
     * @param bool $value
     */
    public function transform($value): bool
    {
        return !$value;
    }

    /**
     * @param bool $value
     */
    public function reverseTransform($value): bool
    {
        return !$value;
    }
}
