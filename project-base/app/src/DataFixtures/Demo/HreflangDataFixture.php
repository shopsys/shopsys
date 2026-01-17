<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;

class HreflangDataFixture extends AbstractReferenceFixture
{
    public function __construct(
        protected readonly SeoSettingFacade $seoSettingFacade,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        $allowedDomainIds = $this->domainsForDataFixtureProvider->getAllowedDemoDataDomainIds();

        if (count($allowedDomainIds) < 2) {
            return; // hreflang is not applicable for a single domain
        }

        $this->seoSettingFacade->setAllAlternativeDomains([$allowedDomainIds]);
    }
}
