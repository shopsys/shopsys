<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Shopsys\FrameworkBundle\Component\Cdn\CdnFacade;
use Symfony\Component\Form\DataTransformerInterface;

class WysiwygCdnDataTransformer implements DataTransformerInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Cdn\CdnFacade $cdnFacade
     */
    public function __construct(private readonly CdnFacade $cdnFacade)
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
        return $this->cdnFacade->replaceUrlsByCdnForAssets($value);
    }
}
