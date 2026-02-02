<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Override;
use Symfony\Component\Form\DataTransformerInterface;

class IndexedBooleansToArrayOfIndexesTransformer implements DataTransformerInterface
{
    #[Override]
    public function transform($value): ?array
    {
        if (!is_array($value)) {
            return null;
        }

        return array_fill_keys($value, true);
    }

    #[Override]
    public function reverseTransform($value): ?array
    {
        if (!is_array($value)) {
            return null;
        }

        return array_keys(array_filter($value));
    }
}
