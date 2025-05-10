<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Symfony\Component\Form\DataTransformerInterface;

class JsonTransformer implements DataTransformerInterface
{
    /**
     * @param string[] $value
     * @return string
     */
    public function transform($value): string
    {
        return json_encode($value ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param string|null $value
     * @return string[]
     */
    public function reverseTransform($value): array
    {
        $value = trim($value ?? '');

        return $value === '' ? [] : json_decode($value, true, flags: JSON_THROW_ON_ERROR);
    }
}
