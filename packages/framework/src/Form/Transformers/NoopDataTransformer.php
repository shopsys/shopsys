<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Override;
use Symfony\Component\Form\DataTransformerInterface;

class NoopDataTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function reverseTransform($value): mixed
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function transform($value): mixed
    {
        return $value;
    }
}
