<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Domain;

use phpmock\MockBuilder;
use phpmock\phpunit\PHPMock;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\DomainDataCreator;
use Shopsys\FrameworkBundle\Component\Domain\Multidomain\MultidomainEntityDataCreator;
use Shopsys\FrameworkBundle\Component\Setting\Exception\SettingValueNotFoundException;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\Setting\SettingValueRepository;
use Shopsys\FrameworkBundle\Component\Translation\TranslatableEntityDataCreator;
use Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Tests\FrameworkBundle\Test\DomainConfigHelper;
use Tests\FrameworkBundle\Unit\TestCase;

class DomainDataCreatorTest extends TestCase
{
    use PHPMock;

    public function testCreateNewDomainsDataNoNewDomain(): void
    {
        $domainConfigs = [
            DomainConfigHelper::getDomainConfig(),
        ];

        $settingMock = $this->createMock(Setting::class);
        $settingMock
            ->expects($this->once())
            ->method('getForDomain')
            ->with($this->equalTo(Setting::DOMAIN_DATA_CREATED), $this->equalTo(1))
            ->willReturn(true);
        $currentAdministratorStub = $this->createStub(CurrentAdministrator::class);

        $domain = new Domain(
            $domainConfigs,
            $settingMock,
            $currentAdministratorStub,
        );

        $settingValueRepositoryStub = $this->createStub(SettingValueRepository::class);
        $multidomainEntityDataCreatorStub = $this->createStub(MultidomainEntityDataCreator::class);
        $translatableEntityDataCreatorStub = $this->createStub(TranslatableEntityDataCreator::class);
        $pricingGroupDataFactoryStub = $this->createStub(PricingGroupDataFactory::class);
        $pricingGroupFacadeStub = $this->createStub(PricingGroupFacade::class);
        $vatDataFactoryStub = $this->createStub(VatDataFactory::class);
        $vatFacadeStub = $this->createStub(VatFacade::class);

        $domainDataCreator = new DomainDataCreator(
            $domain,
            $settingMock,
            $settingValueRepositoryStub,
            $multidomainEntityDataCreatorStub,
            $translatableEntityDataCreatorStub,
            $pricingGroupDataFactoryStub,
            $pricingGroupFacadeStub,
            $vatDataFactoryStub,
            $vatFacadeStub,
        );
        $newDomainsDataCreated = $domainDataCreator->createNewDomainsData();

        $this->assertEquals(0, $newDomainsDataCreated);
    }

    public function testCreateNewDomainsDataOneNewDomain(): void
    {
        $builder = new MockBuilder();
        $builder->setNamespace('\\Shopsys\\FrameworkBundle\\Component\\Domain')->setName('t')->setFunction(
            function () {
                return 'Default VAT group';
            },
        );
        $tFunctionMock = $builder->build();

        $domainConfigs = [
            DomainConfigHelper::getDomainConfig(),
            DomainConfigHelper::getDomainConfig(
                id: Domain::SECOND_DOMAIN_ID,
            ),
        ];

        $settingStub = $this->createStub(Setting::class);
        $settingStub
            ->method('getForDomain')
            ->willReturnCallback(function ($key, $domainId) {
                $this->assertEquals(Setting::DOMAIN_DATA_CREATED, $key);

                if ($domainId === Domain::FIRST_DOMAIN_ID) {
                    return true;
                }

                throw new SettingValueNotFoundException();
            });
        $currentAdministratorStub = $this->createStub(CurrentAdministrator::class);

        $domain = new Domain(
            $domainConfigs,
            $settingStub,
            $currentAdministratorStub,
        );

        $settingValueRepositoryMock = $this->createMock(SettingValueRepository::class);
        $settingValueRepositoryMock
            ->expects($this->any())
            ->method('copyAllMultidomainSettings')
            ->with($this->equalTo(DomainDataCreator::TEMPLATE_DOMAIN_ID), $this->equalTo(2));

        $multidomainEntityDataCreatorMock = $this->createMock(MultidomainEntityDataCreator::class);
        $multidomainEntityDataCreatorMock
            ->expects($this->atLeastOnce())
            ->method('copyAllMultidomainDataForNewDomain')
            ->with($this->equalTo(DomainDataCreator::TEMPLATE_DOMAIN_ID), $this->equalTo(2));

        $translatableEntityDataCreatorStub = $this->createStub(TranslatableEntityDataCreator::class);

        $pricingGroupData = new PricingGroupData();
        $pricingGroupData->name = 'Default';

        $pricingGroupDataFactoryStub = $this->createStub(PricingGroupDataFactory::class);
        $pricingGroupDataFactoryStub
            ->method('create')
            ->willReturn($pricingGroupData);

        $pricingGroup = new PricingGroup($pricingGroupData, 2);

        $this->setValueOfProtectedProperty($pricingGroup, 'id', 1);

        $pricingGroupFacadeMock = $this->createMock(PricingGroupFacade::class);
        $pricingGroupFacadeMock
            ->expects($this->any())->method('create')
            ->with($pricingGroupData, 2)
            ->willReturn($pricingGroup);

        $vatDataFactoryStub = $this->createStub(VatDataFactory::class);
        $vatFacadeStub = $this->createStub(VatFacade::class);

        $domainDataCreator = new DomainDataCreator(
            $domain,
            $settingStub,
            $settingValueRepositoryMock,
            $multidomainEntityDataCreatorMock,
            $translatableEntityDataCreatorStub,
            $pricingGroupDataFactoryStub,
            $pricingGroupFacadeMock,
            $vatDataFactoryStub,
            $vatFacadeStub,
        );

        $tFunctionMock->enable();
        $newDomainsDataCreated = $domainDataCreator->createNewDomainsData();
        $tFunctionMock->disable();

        $this->assertEquals(1, $newDomainsDataCreated);
    }

    public function testCreateNewDomainsDataNewLocale(): void
    {
        $domainConfigWithDataCreated = DomainConfigHelper::getDomainConfig();
        $domainConfigWithNewLocale = DomainConfigHelper::getDomainConfig(
            id: Domain::SECOND_DOMAIN_ID,
            locale: 'en',
        );
        $domainConfigs = [
            $domainConfigWithDataCreated,
            $domainConfigWithNewLocale,
        ];

        $settingStub = $this->createStub(Setting::class);
        $settingStub
            ->method('get')
            ->willReturnCallback(function ($key, $domainId) {
                $this->assertEquals(Setting::DOMAIN_DATA_CREATED, $key);

                if ($domainId === Domain::FIRST_DOMAIN_ID) {
                    return true;
                }

                throw new SettingValueNotFoundException();
            });

        $domainStub = $this->createStub(Domain::class);
        $domainStub
            ->method('getAllIncludingDomainConfigsWithoutDataCreated')
            ->willReturn($domainConfigs);
        $domainStub
            ->method('getAll')
            ->willReturn([$domainConfigWithDataCreated]);
        $domainStub
            ->method('getDomainConfigById')
            ->willReturn($domainConfigWithDataCreated);

        $settingValueRepositoryStub = $this->createStub(SettingValueRepository::class);
        $multidomainEntityDataCreatorStub = $this->createStub(MultidomainEntityDataCreator::class);
        $translatableEntityDataCreatorMock = $this->createMock(TranslatableEntityDataCreator::class);
        $translatableEntityDataCreatorMock
            ->expects($this->any())
            ->method('copyAllTranslatableDataForNewLocale')
            ->with($domainConfigWithDataCreated->getLocale(), $domainConfigWithNewLocale->getLocale());

        $pricingGroupDataFactoryStub = $this->createStub(PricingGroupDataFactory::class);
        $pricingGroupFacadeStub = $this->createStub(PricingGroupFacade::class);

        $vatDataFactoryStub = $this->createStub(VatDataFactory::class);
        $vatFacadeStub = $this->createStub(VatFacade::class);

        $domainDataCreator = new DomainDataCreator(
            $domainStub,
            $settingStub,
            $settingValueRepositoryStub,
            $multidomainEntityDataCreatorStub,
            $translatableEntityDataCreatorMock,
            $pricingGroupDataFactoryStub,
            $pricingGroupFacadeStub,
            $vatDataFactoryStub,
            $vatFacadeStub,
        );

        $domainDataCreator->createNewDomainsData();
    }
}
