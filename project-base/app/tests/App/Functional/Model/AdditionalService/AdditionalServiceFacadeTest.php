<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\AdditionalService;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceData;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceDataFactory;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceFacade;
use Shopsys\FrameworkBundle\Model\AdditionalService\ZboziServiceTypeEnum;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

class AdditionalServiceFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private AdditionalServiceFacade $additionalServiceFacade;

    /**
     * @inject
     */
    private AdditionalServiceDataFactory $additionalServiceDataFactory;

    /**
     * @inject
     */
    private VatFacade $vatFacade;

    public function testCreateAndEditAdditionalService(): void
    {
        $additionalServiceData = $this->additionalServiceDataFactory->create();
        $additionalServiceData->catnum = 'SERVICE-001';
        $additionalServiceData->zboziServiceType = ZboziServiceTypeEnum::FREE_INSTALLATION;
        $additionalServiceData->deliveryDaysExtension = 3;

        foreach (array_keys($additionalServiceData->name) as $locale) {
            $additionalServiceData->name[$locale] = 'Original name';
            $additionalServiceData->feedName[$locale] = 'Original feed name';
            $additionalServiceData->zboziDescription[$locale] = 'Original Zboží.cz description';
            $additionalServiceData->description[$locale] = 'Original description';
        }

        foreach (array_keys($additionalServiceData->enabledByDomainId) as $domainId) {
            $additionalServiceData->pricesIndexedByDomainId[$domainId] = Money::create(100);
        }

        $additionalService = $this->additionalServiceFacade->create($additionalServiceData);
        $additionalServiceId = $additionalService->getId();
        $firstDomainId = array_key_first($additionalServiceData->enabledByDomainId);

        $this->assertSame('SERVICE-001', $additionalService->getCatnum());
        $this->assertSame('Original name', $additionalService->getName());
        $this->assertSame('Original feed name', $additionalService->getFeedName());
        $this->assertSame('Original Zboží.cz description', $additionalService->getZboziDescription());
        $this->assertSame('Original description', $additionalService->getDescription());
        $this->assertSame(ZboziServiceTypeEnum::FREE_INSTALLATION, $additionalService->getZboziServiceType());
        $this->assertSame(3, $additionalService->getDeliveryDaysExtension());
        $this->assertTrue($additionalService->isEnabled($firstDomainId));
        $this->assertTrue($additionalService->isShownInFeeds($firstDomainId));
        $this->assertTrue($additionalService->isProductVatRateUsed($firstDomainId));
        $this->assertNull($additionalService->getVatForDomain($firstDomainId));
        $this->assertThat($additionalService->getPriceForDomain($firstDomainId), $this->logicalNot($this->isNull()));
        $this->assertSame('100', $additionalService->getPriceForDomain($firstDomainId)->getAmount());

        $editData = $this->additionalServiceDataFactory->createFromAdditionalService($additionalService);
        $editData->catnum = 'SERVICE-002';
        $editData->useProductVatRateByDomainId[$firstDomainId] = false;
        $editData->vatsIndexedByDomainId[$firstDomainId] = $this->vatFacade->getDefaultVatForDomain($firstDomainId);
        $editData->pricesIndexedByDomainId[$firstDomainId] = Money::create(200);
        $editData->enabledByDomainId[$firstDomainId] = false;
        $editData->showInFeedsByDomainId[$firstDomainId] = false;
        $this->additionalServiceFacade->edit($additionalServiceId, $editData);

        $editedAdditionalService = $this->additionalServiceFacade->getById($additionalServiceId);
        $this->assertSame('SERVICE-002', $editedAdditionalService->getCatnum());
        $this->assertFalse($editedAdditionalService->isEnabled($firstDomainId));
        $this->assertFalse($editedAdditionalService->isShownInFeeds($firstDomainId));
        $this->assertFalse($editedAdditionalService->isProductVatRateUsed($firstDomainId));
        $this->assertSame(
            $this->vatFacade->getDefaultVatForDomain($firstDomainId)->getId(),
            $editedAdditionalService->getVatForDomain($firstDomainId)->getId(),
        );
        $this->assertSame('200', $editedAdditionalService->getPriceForDomain($firstDomainId)->getAmount());
    }

    public function testAdditionalServiceWithDuplicateCatnumIsRejected(): void
    {
        $this->additionalServiceFacade->create($this->createAdditionalServiceData('SERVICE-DUPLICATE'));

        $this->expectException(UniqueConstraintViolationException::class);

        $this->additionalServiceFacade->create($this->createAdditionalServiceData('SERVICE-DUPLICATE'));
    }

    private function createAdditionalServiceData(string $catnum): AdditionalServiceData
    {
        $additionalServiceData = $this->additionalServiceDataFactory->create();
        $additionalServiceData->catnum = $catnum;

        foreach (array_keys($additionalServiceData->name) as $locale) {
            $additionalServiceData->name[$locale] = 'Service ' . $catnum;
        }

        foreach (array_keys($additionalServiceData->enabledByDomainId) as $domainId) {
            $additionalServiceData->pricesIndexedByDomainId[$domainId] = Money::create(100);
        }

        return $additionalServiceData;
    }

    public function testVatIsNotStoredWhenProductVatRateIsUsed(): void
    {
        $additionalServiceData = $this->additionalServiceDataFactory->create();
        $additionalServiceData->catnum = 'SERVICE-003';
        $firstDomainId = array_key_first($additionalServiceData->enabledByDomainId);

        foreach (array_keys($additionalServiceData->name) as $locale) {
            $additionalServiceData->name[$locale] = 'Service with product VAT rate';
        }

        foreach (array_keys($additionalServiceData->enabledByDomainId) as $domainId) {
            $additionalServiceData->pricesIndexedByDomainId[$domainId] = Money::create(50);
        }

        $additionalServiceData->useProductVatRateByDomainId[$firstDomainId] = true;
        $additionalServiceData->vatsIndexedByDomainId[$firstDomainId] = $this->vatFacade->getDefaultVatForDomain($firstDomainId);

        $additionalService = $this->additionalServiceFacade->create($additionalServiceData);

        $this->assertTrue($additionalService->isProductVatRateUsed($firstDomainId));
        $this->assertNull($additionalService->getVatForDomain($firstDomainId));
    }

    public function testMissingVatFallsBackToProductVatRate(): void
    {
        $firstDomainId = array_key_first($this->additionalServiceDataFactory->create()->enabledByDomainId);
        $serviceWithMissingVat = $this->createAdditionalServiceWithOwnVatRate('SERVICE-MISSING-VAT', $firstDomainId);
        $serviceWithOwnVat = $this->createAdditionalServiceWithOwnVatRate('SERVICE-OWN-VAT', $firstDomainId);

        $this->em->getConnection()->executeStatement(
            'UPDATE additional_service_domains SET vat_id = NULL WHERE additional_service_id = :additionalServiceId AND domain_id = :domainId',
            [
                'additionalServiceId' => $serviceWithMissingVat->getId(),
                'domainId' => $firstDomainId,
            ],
        );

        $this->additionalServiceFacade->useProductVatRateWhereVatIsMissing($firstDomainId);
        $this->em->clear();

        $repairedAdditionalService = $this->additionalServiceFacade->getById($serviceWithMissingVat->getId());
        $this->assertTrue($repairedAdditionalService->isProductVatRateUsed($firstDomainId));
        $this->assertNull($repairedAdditionalService->getVatForDomain($firstDomainId));

        $untouchedAdditionalService = $this->additionalServiceFacade->getById($serviceWithOwnVat->getId());
        $this->assertFalse($untouchedAdditionalService->isProductVatRateUsed($firstDomainId));
        $this->assertNotNull($untouchedAdditionalService->getVatForDomain($firstDomainId));
    }

    private function createAdditionalServiceWithOwnVatRate(string $catnum, int $domainId): AdditionalService
    {
        $additionalServiceData = $this->additionalServiceDataFactory->create();
        $additionalServiceData->catnum = $catnum;

        foreach (array_keys($additionalServiceData->name) as $locale) {
            $additionalServiceData->name[$locale] = 'Service with own VAT rate';
        }

        foreach (array_keys($additionalServiceData->enabledByDomainId) as $enabledDomainId) {
            $additionalServiceData->pricesIndexedByDomainId[$enabledDomainId] = Money::create(50);
        }

        $additionalServiceData->useProductVatRateByDomainId[$domainId] = false;
        $additionalServiceData->vatsIndexedByDomainId[$domainId] = $this->vatFacade->getDefaultVatForDomain($domainId);

        return $this->additionalServiceFacade->create($additionalServiceData);
    }
}
