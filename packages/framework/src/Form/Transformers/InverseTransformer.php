<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Override;
use Symfony\Component\Form\DataTransformerInterface;

class InverseTransformer implements DataTransformerInterface
{
    /**
     * @param bool $value
     * @return bool
     */
    #[Override]
    public function transform($value): bool
    {
        return !$value;
    }

    /**
     * @param bool $value
     * @return bool
     */
    #[Override]
    public function reverseTransform($value): bool
    {
        return !$value;
    }
}
