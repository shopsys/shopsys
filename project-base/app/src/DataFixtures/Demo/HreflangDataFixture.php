<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;

class HreflangDataFixture extends AbstractReferenceFixture
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade $seoSettingFacade
     */
    public function __construct(
        protected readonly SeoSettingFacade $seoSettingFacade,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        if (
            !$this->domainsForDataFixtureProvider->isDomainIdAllowed(1) ||
            !$this->domainsForDataFixtureProvider->isDomainIdAllowed(2)
        ) {
            return;
        }

        $this->seoSettingFacade->setAllAlternativeDomains([[1, 2]]);
    }
}
