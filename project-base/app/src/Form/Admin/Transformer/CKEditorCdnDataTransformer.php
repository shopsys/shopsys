<?php

declare(strict_types=1);

namespace App\Form\Admin\Transformer;

use App\Component\Image\ImageFacade;
use Symfony\Component\Form\DataTransformerInterface;

class CKEditorCdnDataTransformer implements DataTransformerInterface
{
    /**
     * @param \App\Component\Image\ImageFacade $imageFacade
     */
    public function __construct(private readonly ImageFacade $imageFacade)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        return $this->imageFacade->replaceImageUrlsByCdn($value);
    }
}
