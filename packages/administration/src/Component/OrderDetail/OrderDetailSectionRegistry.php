<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\OrderDetail;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderDetailSectionRegistry
{
    /**
     * @param iterable<\Shopsys\AdministrationBundle\Component\OrderDetail\OrderDetailSectionProviderInterface> $sectionProviders
     */
    public function __construct(
        #[AutowireIterator('shopsys.order_detail_section_provider')]
        protected readonly iterable $sectionProviders,
    ) {
    }

    public function getSection(string $sectionId): OrderDetailSection
    {
        return $this->getSections()[$sectionId] ?? throw new NotFoundHttpException();
    }

    /**
     * @return array<string, \Shopsys\AdministrationBundle\Component\OrderDetail\OrderDetailSection>
     */
    protected function getSections(): array
    {
        $sections = [];

        foreach ($this->sectionProviders as $sectionProvider) {
            foreach ($sectionProvider->getSections() as $section) {
                $this->assertValidSectionId($section->getId());

                $sections[$section->getId()] = $section;
            }
        }

        return $sections;
    }

    protected function assertValidSectionId(string $sectionId): void
    {
        if (ctype_alnum($sectionId)) {
            return;
        }

        throw new InvalidArgumentException(sprintf('Invalid order detail section ID "%s".', $sectionId));
    }
}
