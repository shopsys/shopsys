<?php

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Symfony\Component\Form\DataTransformerInterface;

class InverseTransformer implements DataTransformerInterface
{
    public function transform(bool $value): bool
    {
        return !$value;
    }
    
    public function reverseTransform(bool $value): bool
    {
        return !$value;
    }
}
