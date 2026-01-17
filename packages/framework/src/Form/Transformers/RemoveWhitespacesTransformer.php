<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Override;
use Symfony\Component\Form\DataTransformerInterface;

class RemoveWhitespacesTransformer implements DataTransformerInterface
{
    /**
     * @param string|null $value
     */
    #[Override]
    public function transform($value): ?string
    {
        return $value;
    }

    /**
     * @param string|null $value
     */
    #[Override]
    public function reverseTransform($value): ?string
    {
        return $value === null ? null : preg_replace('/\s/', '', $value);
    }
}
