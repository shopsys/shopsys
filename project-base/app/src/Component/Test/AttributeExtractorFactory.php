<?php

declare(strict_types=1);

namespace App\Component\Test;

use Zalas\Injector\Service\Extractor;
use Zalas\Injector\Service\ExtractorFactory;

/**
 * Factory for creating service extractors that support both @inject annotation and #[Inject] attribute
 * 
 * @IgnoreAnnotation("inject")
 */
final class AttributeExtractorFactory implements ExtractorFactory
{
    /**
     * @param array<class-string> $ignoredClasses
     */
    public function __construct(
        private readonly array $ignoredClasses = [],
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function create(): Extractor
    {
        return new AttributeServiceExtractor($this->ignoredClasses);
    }
}