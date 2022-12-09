<?php

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Symfony\Component\Form\DataTransformerInterface;

class EmptyWysiwygTransformer implements DataTransformerInterface
{
    /**
     * @param mixed $value
     * @return mixed
     */
    public function reverseTransform($value): mixed
    {
        return $value;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function transform($value): mixed
    {
        $trimmedValue = strip_tags(preg_replace('/\s|\&nbsp\;/', '', $value));
        if ($trimmedValue === '') {
            return null;
        }
        return $value;
    }
}
