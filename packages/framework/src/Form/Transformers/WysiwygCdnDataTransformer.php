<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Override;
use Shopsys\FrameworkBundle\Component\Cdn\CdnFacade;
use Symfony\Component\Form\DataTransformerInterface;

class WysiwygCdnDataTransformer implements DataTransformerInterface
{
    public function __construct(protected readonly CdnFacade $cdnFacade)
    {
    }

    #[Override]
    public function transform($value): mixed
    {
        return $value;
    }

    /**
     * @param mixed $value
     */
    #[Override]
    public function reverseTransform($value): ?string
    {
        return $this->cdnFacade->replaceUrlsByCdnForAssets($value);
    }
}
