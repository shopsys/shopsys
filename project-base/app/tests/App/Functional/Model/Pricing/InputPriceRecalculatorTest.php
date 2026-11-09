<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Pricing;

use Shopsys\FrameworkBundle\Model\Pricing\InputPriceRecalculator;
use Tests\App\Test\TableRowTestHelper;
use Tests\App\Test\TransactionFunctionalTestCase;

final class InputPriceRecalculatorTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private InputPriceRecalculator $inputPriceRecalculator;

    /**
     * @inject
     */
    private TableRowTestHelper $tableRowTestHelper;

    public function testRecalculationDoesNotRewriteUnchangedPaymentPrices(): void
    {
        $this->assertRecalculationDoesNotRewriteUnchangedPrices('payment_prices', ['payment_id', 'domain_id']);
    }

    public function testRecalculationDoesNotRewriteUnchangedTransportPrices(): void
    {
        $this->assertRecalculationDoesNotRewriteUnchangedPrices('transport_prices', ['id']);
    }

    /**
     * @param string[] $primaryKeyColumns
     */
    private function assertRecalculationDoesNotRewriteUnchangedPrices(
        string $tableName,
        array $primaryKeyColumns,
    ): void {
        $pricesBeforeRecalculation = $this->tableRowTestHelper->getColumnValuesIndexedByPrimaryKey($tableName, $primaryKeyColumns, 'price');
        $rowVersionsBeforeRecalculation = $this->tableRowTestHelper->getRowVersionsIndexedByPrimaryKey($tableName, $primaryKeyColumns);

        $this->inputPriceRecalculator->recalculateToInputPricesWithVat();
        $this->em->clear();

        $pricesAfterRecalculation = $this->tableRowTestHelper->getColumnValuesIndexedByPrimaryKey($tableName, $primaryKeyColumns, 'price');
        $unchangedPricePrimaryKeys = array_flip(array_keys(array_intersect_assoc($pricesBeforeRecalculation, $pricesAfterRecalculation)));
        $rowVersionsAfterRecalculation = $this->tableRowTestHelper->getRowVersionsIndexedByPrimaryKey($tableName, $primaryKeyColumns);

        $this->assertNotSame([], $unchangedPricePrimaryKeys);
        $this->assertSame(
            array_intersect_key($rowVersionsBeforeRecalculation, $unchangedPricePrimaryKeys),
            array_intersect_key($rowVersionsAfterRecalculation, $unchangedPricePrimaryKeys),
        );
    }
}
