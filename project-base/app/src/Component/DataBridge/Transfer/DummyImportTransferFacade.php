<?php

declare(strict_types=1);

namespace App\Component\DataBridge\Transfer;

use Generator;
use Override;

class DummyImportTransferFacade extends AbstractBridgeImportTransfer
{
    /**
     * @param array $bridgeData
     */
    #[Override]
    protected function processItem(array $bridgeData): void
    {
        // Implement processItem() method.
    }

    #[Override]
    protected function doBeforeTransfer(): void
    {
        // Implement doBeforeTransfer() method.
    }

    #[Override]
    protected function doAfterTransfer(): void
    {
        // Implement doAfterTransfer() method.
    }

    /**
     * @return \Generator
     */
    #[Override]
    protected function getData(): Generator
    {
        yield '';
    }

    /**
     * @return string
     */
    #[Override]
    public function getTransferName(): string
    {
        return 'Dummy transfer';
    }

    /**
     * @return string
     */
    #[Override]
    public function getTransferIdentifier(): string
    {
        return 'dummy_transfer';
    }
}
