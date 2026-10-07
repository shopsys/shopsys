<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Seo\OrganizationDataFactory;
use Shopsys\FrameworkBundle\Model\Seo\OrganizationFacade;

final class OrganizationDataFixture extends AbstractReferenceFixture
{
    public const string ORGANIZATION = 'organization';
    public const string ORGANIZATION_NAME = 'Shopsys s.r.o.';
    public const string ORGANIZATION_COMPANY_TAX_NUMBER = 'CZ12345678';
    public const string ORGANIZATION_CITY = 'Ostrava';

    public function __construct(
        private readonly OrganizationFacade $organizationFacade,
        private readonly OrganizationDataFactory $organizationDataFactory,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $organizationData = $this->organizationDataFactory->findOrCreateForDomain($domainConfig->getId());
            $organizationData->name = self::ORGANIZATION_NAME;
            $organizationData->companyNumber = '12345678';
            $organizationData->companyTaxNumber = self::ORGANIZATION_COMPANY_TAX_NUMBER;
            $organizationData->companyVatNumber = 'CZ12345678';
            $organizationData->description = t(
                'Shopsys Platform is an e-commerce platform for growing online stores. This demo shop presents its features on a catalog of electronics, books, toys and more.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $domainConfig->getLocale(),
            );
            $organizationData->street = 'Koksární 10';
            $organizationData->city = self::ORGANIZATION_CITY;
            $organizationData->postcode = '70200';
            $organizationData->country = 'CZ';

            $organization = $this->organizationFacade->edit($domainConfig->getId(), $organizationData);
            $this->addReferenceForDomain(self::ORGANIZATION, $organization, $domainConfig->getId());
        }
    }
}
