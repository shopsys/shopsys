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
    public function __construct(protected readonly CdnFacade $cdnFacade)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value): mixed
    {
        return $value;
    }

    /**
     * @param mixed $value
     * @return string|null
     */
    public function reverseTransform($value): ?string
    {
        return $this->cdnFacade->replaceUrlsByCdnForAssets($value);
    }
}
