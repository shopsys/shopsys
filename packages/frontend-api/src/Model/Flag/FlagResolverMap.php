<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Flag;

use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;

class FlagResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly DataLoaderInterface $flagSlugBatchLoader,
        protected readonly DataLoaderInterface $flagHreflangLinksBatchLoader,
    ) {
    }

    /**
     * @return array<string, array<string, callable>>
     */
    #[Override]
    protected function map(): array
    {
        return [
            'Flag' => [
                'name' => function (Flag $flag) {
                    return $flag->getName($this->domain->getLocale()) ?? '';
                },
                'slug' => function (Flag $flag) {
                    return $this->flagSlugBatchLoader->load($flag->getId());
                },
                'hreflangLinks' => function (Flag $flag) {
                    return $this->flagHreflangLinksBatchLoader->load($flag);
                },
                'seo' => fn (Flag $flag) => $flag->getSeoAttributes($this->domain->getId()),
            ],
        ];
    }
}
