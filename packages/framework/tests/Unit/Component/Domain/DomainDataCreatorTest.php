<?php

namespace Tests\FrameworkBundle\Unit\Component\Domain;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\DomainDataCreator;
use Shopsys\FrameworkBundle\Component\Domain\Multidomain\MultidomainEntityDataCreator;
use Shopsys\FrameworkBundle\Component\Setting\Exception\SettingValueNotFoundException;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\Setting\SettingValueRepository;
use Shopsys\FrameworkBundle\Component\Translation\TranslatableEntityDataCreator;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Tests\FrameworkBundle\Unit\TestCase;

class DomainDataCreatorTest extends TestCase
{
    public function testCreateNewDomainsDataNoNewDomain()
    {
        $domainConfigs = [
            new DomainConfig(Domain::FIRST_DOMAIN_ID, 'http://example.com:8080', 'example', 'cs'),
        ];

        $settingMock = $this->createMock(Setting::class);
        $settingMock
            ->expects($this->once())
            ->method('getForDomain')
            ->with($this->equalTo(Setting::DOMAIN_DATA_CREATED), $this->equalTo(1))
            ->willReturn(true);

        $domain = new Domain($domainConfigs, $settingMock);

        $settingValueRepositoryMock = $this->createMock(SettingValueRepository::class);
        $multidomainEntityDataCreatorMock = $this->createMock(MultidomainEntityDataCreator::class);
        $translatableEntityDataCreatorMock = $this->createMock(TranslatableEntityDataCreator::class);
        $pricingGroupDataFactoryMock = $this->createMock(PricingGroupDataFactory::class);
        $pricingGroupFacadeMock = $this->createMock(PricingGroupFacade::class);
        $vatDataFactoryMock = $this->createMock(VatDataFactory::class);
        $vatFacadeMock = $this->createMock(VatFacade::class);

        $domainDataCreator = new DomainDataCreator(
            $domain,
            $settingMock,
            $settingValueRepositoryMock,
            $multidomainEntityDataCreatorMock,
            $translatableEntityDataCreatorMock,
            $pricingGroupDataFactoryMock,
            $pricingGroupFacadeMock,
            $vatDataFactoryMock,
            $vatFacadeMock,
        );
        $newDomainsDataCreated = $domainDataCreator->createNewDomainsData();

        $this->assertEquals(0, $newDomainsDataCreated);
    }

    public function testCreateNewDomainsDataOneNewDomain()
    {
        $domainConfigs = [
            new DomainConfig(Domain::FIRST_DOMAIN_ID, 'http://example.com:8080', 'example', 'cs'),
            new DomainConfig(Domain::SECOND_DOMAIN_ID, 'http://example.com:8080', 'example', 'cs'),
        ];

        $settingMock = $this->createMock(Setting::class);
        $settingMock
            ->method('getForDomain')
            ->willReturnCallback(function ($key, $domainId) {
                $this->assertEquals(Setting::DOMAIN_DATA_CREATED, $key);

                if ($domainId === Domain::FIRST_DOMAIN_ID) {
                    return true;
                }

                throw new SettingValueNotFoundException();
            });

        $domain = new Domain($domainConfigs, $settingMock);

        $settingValueRepositoryMock = $this->createMock(SettingValueRepository::class);
        $settingValueRepositoryMock
            ->expects($this->any())
            ->method('copyAllMultidomainSettings')
            ->with($this->equalTo(DomainDataCreator::TEMPLATE_DOMAIN_ID), $this->equalTo(2));

        $multidomainEntityDataCreatorMock = $this->createMock(MultidomainEntityDataCreator::class);
        $multidomainEntityDataCreatorMock
            ->method('copyAllMultidomainDataForNewDomain')
            ->with($this->equalTo(DomainDataCreator::TEMPLATE_DOMAIN_ID), $this->equalTo(2));

        $translatableEntityDataCreatorMock = $this->createMock(TranslatableEntityDataCreator::class);

        $pricingGroupData = new PricingGroupData();
        $pricingGroupData->name = 'Default';

        $pricingGroupDataFactoryMock = $this->createMock(PricingGroupDataFactory::class);
        $pricingGroupDataFactoryMock
            ->method('create')
            ->willReturn($pricingGroupData);

        $pricingGroup = new PricingGroup($pricingGroupData, 2);

        $this->setValueOfProtectedProperty($pricingGroup, 'id', 1);

        $pricingGroupFacadeMock = $this->createMock(PricingGroupFacade::class);
        $pricingGroupFacadeMock
            ->method('create')
            ->with($pricingGroupData, 2)
            ->willReturn($pricingGroup);

        $vatDataFactoryMock = $this->createMock(VatDataFactory::class);
        $vatFacadeMock = $this->createMock(VatFacade::class);

        $domainDataCreator = new DomainDataCreator(
            $domain,
            $settingMock,
            $settingValueRepositoryMock,
            $multidomainEntityDataCreatorMock,
            $translatableEntityDataCreatorMock,
            $pricingGroupDataFactoryMock,
            $pricingGroupFacadeMock,
            $vatDataFactoryMock,
            $vatFacadeMock,
        );
        $newDomainsDataCreated = $domainDataCreator->createNewDomainsData();

        $this->assertEquals(1, $newDomainsDataCreated);
    }

    public function testCreateNewDomainsDataNewLocale()
    {
        $domainConfigWithDataCreated = new DomainConfig(
            Domain::FIRST_DOMAIN_ID,
            'http://example.com:8080',
            'example',
            'cs',
        );
        $domainConfigWithNewLocale = new DomainConfig(
            Domain::SECOND_DOMAIN_ID,
            'http://example.com:8080',
            'example',
            'en',
        );
        $domainConfigs = [
            $domainConfigWithDataCreated,
            $domainConfigWithNewLocale,
        ];

        $settingMock = $this->createMock(Setting::class);
        $settingMock
            ->method('get')
            ->willReturnCallback(function ($key, $domainId) {
                $this->assertEquals(Setting::DOMAIN_DATA_CREATED, $key);

                if ($domainId === Domain::FIRST_DOMAIN_ID) {
                    return true;
                }

                throw new SettingValueNotFoundException();
            });

        $domainMock = $this->createMock(Domain::class);
        $domainMock
            ->expects($this->any())
            ->method('getAllIncludingDomainConfigsWithoutDataCreated')
            ->willReturn($domainConfigs);
        $domainMock
            ->expects($this->any())
            ->method('getAll')
            ->willReturn([$domainConfigWithDataCreated]);
        $domainMock
            ->expects($this->any())
            ->method('getDomainConfigById')
            ->willReturn($domainConfigWithDataCreated);

        $settingValueRepositoryMock = $this->createMock(SettingValueRepository::class);
        $multidomainEntityDataCreatorMock = $this->createMock(MultidomainEntityDataCreator::class);
        $translatableEntityDataCreatorMock = $this->createMock(TranslatableEntityDataCreator::class);
        $translatableEntityDataCreatorMock
            ->expects($this->any())
            ->method('copyAllTranslatableDataForNewLocale')
            ->with($domainConfigWithDataCreated->getLocale(), $domainConfigWithNewLocale->getLocale());

        $pricingGroupDataFactoryMock = $this->createMock(PricingGroupDataFactory::class);
        $pricingGroupFacadeMock = $this->createMock(PricingGroupFacade::class);

        $vatDataFactoryMock = $this->createMock(VatDataFactory::class);
        $vatFacadeMock = $this->createMock(VatFacade::class);

        $domainDataCreator = new DomainDataCreator(
            $domainMock,
            $settingMock,
            $settingValueRepositoryMock,
            $multidomainEntityDataCreatorMock,
            $translatableEntityDataCreatorMock,
            $pricingGroupDataFactoryMock,
            $pricingGroupFacadeMock,
            $vatDataFactoryMock,
            $vatFacadeMock,
        );

        $domainDataCreator->createNewDomainsData();
    }
}
