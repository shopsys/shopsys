<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Seo;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\Form\DataTransformerInterface;

class DomainConfigCollectionToDomainIdsTransformer implements DataTransformerInterface
{
    public function __construct(
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param int[][] $value
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[][]
     */
    #[Override]
    public function transform($value): array
    {
        return array_map(
            fn (array $domainIds) => array_map(
                fn (int $domainId) => $this->domain->getDomainConfigById($domainId),
                $domainIds,
            ),
            $value,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[][] $value
     * @return int[][]
     */
    #[Override]
    public function reverseTransform($value): array
    {
        return array_map(
            static fn (array $domainConfigs) => array_map(
                static fn (DomainConfig $domainConfig) => $domainConfig->getId(),
                $domainConfigs,
            ),
            $value,
        );
    }
}
