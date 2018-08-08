<?php

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Symfony\Component\Form\DataTransformerInterface;

class RemoveWhitespacesTransformer implements DataTransformerInterface
{
    /**
     * @param string|null $value
     */
    public function transform(?string $value): ?string
    {
        return $value;
    }

    /**
     * @param string|null $value
     */
    public function reverseTransform(?string $value): ?string
    {
        return $value === null ? null : preg_replace('/\s/', '', $value);
    }
}
