<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Stock;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Stock\Exception\DefaultStockNotEnabledException;
use Shopsys\FrameworkBundle\Model\Stock\Stock;
use Shopsys\FrameworkBundle\Model\Stock\StockDataFactory;
use Shopsys\FrameworkBundle\Model\Stock\StockFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

final class StockDomainDefaultTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private StockFacade $stockFacade;

    /**
     * @inject
     */
    private StockDataFactory $stockDataFactory;

    public function testIsDefaultReturnsPerDomainValue(): void
    {
        $stockData = $this->stockDataFactory->create();
        $stockData->name = 'Test stock';

        foreach ($this->domain->getAllIds() as $domainId) {
            $stockData->isEnabledByDomain[$domainId] = true;
            $stockData->isDefaultByDomain[$domainId] = $domainId === Domain::FIRST_DOMAIN_ID;
        }

        $stock = $this->stockFacade->create($stockData);
        $this->em->clear();

        $refreshedStock = $this->stockFacade->getById($stock->getId());

        $this->assertTrue($refreshedStock->isDefault(Domain::FIRST_DOMAIN_ID));

        if (count($this->domain->getAllIds()) <= 1) {
            return;
        }

        $secondDomainId = $this->domain->getAllIds()[1];
        $this->assertFalse($refreshedStock->isDefault($secondDomainId));
    }

    public function testIsDefaultOnAnyDomainReturnsTrueWhenDefaultOnOneDomain(): void
    {
        $stockData = $this->stockDataFactory->create();
        $stockData->name = 'Test stock';

        foreach ($this->domain->getAllIds() as $domainId) {
            $stockData->isEnabledByDomain[$domainId] = true;
            $stockData->isDefaultByDomain[$domainId] = $domainId === Domain::FIRST_DOMAIN_ID;
        }

        $stock = $this->stockFacade->create($stockData);
        $this->em->clear();

        $refreshedStock = $this->stockFacade->getById($stock->getId());

        $this->assertTrue($refreshedStock->isDefaultOnAnyDomain());
    }

    public function testIsDefaultOnAnyDomainReturnsFalseWhenNotDefault(): void
    {
        $stockData = $this->stockDataFactory->create();
        $stockData->name = 'Test stock';

        foreach ($this->domain->getAllIds() as $domainId) {
            $stockData->isEnabledByDomain[$domainId] = true;
            $stockData->isDefaultByDomain[$domainId] = false;
        }

        $stock = $this->stockFacade->create($stockData);
        $this->em->clear();

        $refreshedStock = $this->stockFacade->getById($stock->getId());

        $this->assertFalse($refreshedStock->isDefaultOnAnyDomain());
    }

    public function testEditDefaultAffectsOnlyGivenDomain(): void
    {
        if (count($this->domain->getAllIds()) < 2) {
            $this->markTestSkipped('Test requires at least two domains.');
        }

        $secondDomainId = $this->domain->getAllIds()[1];

        $previousDefaultOnSecondDomain = $this->findDefaultStockForDomain($secondDomainId);
        $this->assertNotNull($previousDefaultOnSecondDomain, 'Fixture should have a default stock on domain 2');

        $stockData = $this->stockDataFactory->create();
        $stockData->name = 'New default';

        foreach ($this->domain->getAllIds() as $domainId) {
            $stockData->isEnabledByDomain[$domainId] = true;
            $stockData->isDefaultByDomain[$domainId] = false;
        }

        $newStock = $this->stockFacade->create($stockData);

        $editData = $this->stockDataFactory->createFromStock($newStock);
        $editData->isDefaultByDomain[Domain::FIRST_DOMAIN_ID] = true;
        $this->stockFacade->edit($newStock->getId(), $editData);
        $this->em->clear();

        $refreshedNew = $this->stockFacade->getById($newStock->getId());
        $this->assertTrue($refreshedNew->isDefault(Domain::FIRST_DOMAIN_ID));
        $this->assertFalse($refreshedNew->isDefault($secondDomainId));

        $refreshedPreviousOnSecond = $this->stockFacade->getById($previousDefaultOnSecondDomain->getId());
        $this->assertTrue($refreshedPreviousOnSecond->isDefault($secondDomainId));
    }

    public function testEditDefaultUnsetsOtherDefaultOnSameDomain(): void
    {
        $stockData = $this->stockDataFactory->create();
        $stockData->name = 'New default';

        foreach ($this->domain->getAllIds() as $domainId) {
            $stockData->isEnabledByDomain[$domainId] = true;
            $stockData->isDefaultByDomain[$domainId] = false;
        }

        $newStock = $this->stockFacade->create($stockData);

        $previousDefaultId = null;

        foreach ($this->stockFacade->getAllStocks() as $stock) {
            if ($stock->isDefault(Domain::FIRST_DOMAIN_ID)) {
                $previousDefaultId = $stock->getId();

                break;
            }
        }
        $this->assertNotNull($previousDefaultId, 'Fixture should have a default stock on domain 1');

        $editData = $this->stockDataFactory->createFromStock($newStock);
        $editData->isDefaultByDomain[Domain::FIRST_DOMAIN_ID] = true;
        $this->stockFacade->edit($newStock->getId(), $editData);
        $this->em->clear();

        $refreshedPrevious = $this->stockFacade->getById($previousDefaultId);
        $this->assertFalse($refreshedPrevious->isDefault(Domain::FIRST_DOMAIN_ID));

        $refreshedNew = $this->stockFacade->getById($newStock->getId());
        $this->assertTrue($refreshedNew->isDefault(Domain::FIRST_DOMAIN_ID));
    }

    public function testGetDomainIdsWithoutDefaultStockDetectsMissingDefault(): void
    {
        $defaultStock = $this->findDefaultStockForDomain(Domain::FIRST_DOMAIN_ID);
        $this->assertNotNull($defaultStock, 'Fixture should have a default stock on domain 1');

        $stockData = $this->stockDataFactory->createFromStock($defaultStock);
        $stockData->isDefaultByDomain[Domain::FIRST_DOMAIN_ID] = false;

        $this->stockFacade->edit($defaultStock->getId(), $stockData);
        $this->em->clear();

        $domainIdsWithoutDefault = $this->stockFacade->getDomainIdsWithoutDefaultStock($this->domain->getAllIds());

        $this->assertContains(Domain::FIRST_DOMAIN_ID, $domainIdsWithoutDefault);
    }

    public function testDefaultOnDisabledDomainThrowsException(): void
    {
        $stockData = $this->stockDataFactory->create();
        $stockData->name = 'Invalid stock';

        foreach ($this->domain->getAllIds() as $domainId) {
            $stockData->isEnabledByDomain[$domainId] = false;
            $stockData->isDefaultByDomain[$domainId] = false;
        }
        $stockData->isDefaultByDomain[Domain::FIRST_DOMAIN_ID] = true;

        $this->expectException(DefaultStockNotEnabledException::class);
        $this->stockFacade->create($stockData);
    }

    public function testGetDefaultButDisabledStockDomainsDetectsInconsistency(): void
    {
        $stockData = $this->stockDataFactory->create();
        $stockData->name = 'Inconsistent stock';

        foreach ($this->domain->getAllIds() as $domainId) {
            $stockData->isEnabledByDomain[$domainId] = true;
            $stockData->isDefaultByDomain[$domainId] = false;
        }
        $stockData->isDefaultByDomain[Domain::FIRST_DOMAIN_ID] = true;

        $stock = $this->stockFacade->create($stockData);

        // Directly disable the domain via SQL to bypass facade validation
        $this->em->getConnection()->executeStatement(
            'UPDATE stock_domains SET is_enabled = false WHERE stock_id = :stockId AND domain_id = :domainId',
            [
                'stockId' => $stock->getId(),
                'domainId' => Domain::FIRST_DOMAIN_ID,
            ],
        );
        $this->em->clear();

        $defaultButDisabled = $this->stockFacade->getDefaultButDisabledStockDomains();

        $found = array_filter(
            $defaultButDisabled,
            static fn (array $row) => (int)$row['stockId'] === $stock->getId()
                && (int)$row['domainId'] === Domain::FIRST_DOMAIN_ID,
        );

        $this->assertNotEmpty($found, 'Should detect stock that is default but disabled on a domain');
    }

    private function findDefaultStockForDomain(int $domainId): ?Stock
    {
        foreach ($this->stockFacade->getAllStocks() as $stock) {
            if ($stock->isDefault($domainId)) {
                return $stock;
            }
        }

        return null;
    }
}
