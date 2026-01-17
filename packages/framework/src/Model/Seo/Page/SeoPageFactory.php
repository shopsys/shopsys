<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo\Page;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class SeoPageFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(SeoPageData $data): SeoPage
    {
        $entityClassName = $this->entityNameResolver->resolve(SeoPage::class);

        return new $entityClassName($data);
    }
}
