<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Symfony\Component\Form\DataTransformerInterface;

class NoopDataTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value): mixed
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value): mixed
    {
        return $value;
    }
}
