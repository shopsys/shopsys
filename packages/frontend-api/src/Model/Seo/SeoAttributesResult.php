<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Seo;

class SeoAttributesResult
{
    public function __construct(
        public readonly ?string $title,
        public readonly ?string $metaDescription,
        public readonly ?string $h1,
        public readonly ?string $metaRobots,
        public readonly ?string $canonicalUrl,
    ) {
    }
}
