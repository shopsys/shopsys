<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo\Page;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class SeoPageFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageData $data
     * @return \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage
     */
    public function create(SeoPageData $data): SeoPage
    {
        $entityClassName = $this->entityNameResolver->resolve(SeoPage::class);

        return new $entityClassName($data);
    }
}
