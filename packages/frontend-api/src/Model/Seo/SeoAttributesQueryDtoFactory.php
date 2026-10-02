<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Seo;

class SeoAttributesQueryDtoFactory
{
    public function create(
        ?string $title,
        ?string $metaDescription,
        ?string $h1,
        ?string $metaRobots,
        ?string $canonicalUrl,
    ): SeoAttributesQueryDto {
        return new SeoAttributesQueryDto($title, $metaDescription, $h1, $metaRobots, $canonicalUrl);
    }
}
