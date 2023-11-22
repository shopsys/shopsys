<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Domain;

use DateTimeZone;
use phpmock\MockBuilder;
use phpmock\phpunit\PHPMock;
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
    use PHPMock;

    public function testCreateNewDomainsDataNoNewDomain(): void
    {
        $defaultTimeZone = new DateTimeZone('Europe/Prague');
        $domainConfigs = [
            new DomainConfig(Domain::FIRST_DOMAIN_ID, 'http://example.com:8080', 'example', 'cs', $defaultTimeZone),
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

    /**
     * @return 'Default VAT group'|true
     */
    public function testCreateNewDomainsDataOneNewDomain(): string|bool
    {
        $defaultTimeZone = new DateTimeZone('Europe/Prague');

        $builder = new MockBuilder();
        $builder->setNamespace('\\Shopsys\\FrameworkBundle\\Component\\Domain')->setName('t')->setFunction(
            function () {
                return 'Default VAT group';
            },
        );
        $tFunctionMock = $builder->build();

        $domainConfigs = [
            new DomainConfig(Domain::FIRST_DOMAIN_ID, 'http://example.com:8080', 'example', 'cs', $defaultTimeZone),
            new DomainConfig(Domain::SECOND_DOMAIN_ID, 'http://example.com:8080', 'example', 'cs', $defaultTimeZone),
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

        $tFunctionMock->enable();
        $newDomainsDataCreated = $domainDataCreator->createNewDomainsData();
        $tFunctionMock->disable();

        $this->assertEquals(1, $newDomainsDataCreated);
    }

    /**
     * @return true
     */
    public function testCreateNewDomainsDataNewLocale(): bool
    {
        $defaultTimeZone = new DateTimeZone('Europe/Prague');
        $domainConfigWithDataCreated = new DomainConfig(
            Domain::FIRST_DOMAIN_ID,
            'http://example.com:8080',
            'example',
            'cs',
            $defaultTimeZone,
        );
        $domainConfigWithNewLocale = new DomainConfig(
            Domain::SECOND_DOMAIN_ID,
            'http://example.com:8080',
            'example',
            'en',
            $defaultTimeZone,
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
