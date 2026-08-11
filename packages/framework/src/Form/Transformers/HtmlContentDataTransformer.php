<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Override;
use Shopsys\FrameworkBundle\Component\Html\HtmlContentProcessor;
use Symfony\Component\Form\DataTransformerInterface;

class HtmlContentDataTransformer implements DataTransformerInterface
{
    public function __construct(protected readonly HtmlContentProcessor $htmlContentProcessor)
    {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function transform($value): mixed
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function reverseTransform($value): ?string
    {
        return $this->htmlContentProcessor->process($value);
    }
}
