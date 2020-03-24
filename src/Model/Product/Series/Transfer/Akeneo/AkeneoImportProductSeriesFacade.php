<?php

declare(strict_types=1);

namespace App\Model\Product\Series\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\AbstractAkeneoImportTransfer;
use App\Component\Akeneo\Transfer\AkeneoImportTransferDependency;
use App\Model\Product\Series\ProductSeries;
use App\Model\Product\Series\ProductSeriesFacade;

class AkeneoImportProductSeriesFacade extends AbstractAkeneoImportTransfer
{
    /**
     * @var \App\Model\Product\Series\Transfer\Akeneo\ProductSeriesTransferAkeneoFacade
     */
    private $productSeriesTransferAkeneoFacade;

    /**
     * @var \App\Model\Product\Series\Transfer\Akeneo\ProductSeriesTransferAkeneoValidator
     */
    private $productSeriesTransferAkeneoValidator;

    /**
     * @var \App\Model\Product\Series\ProductSeriesFacade
     */
    private $productSeriesFacade;

    /**
     * @var \App\Model\Product\Series\Transfer\Akeneo\ProductSeriesTransferAkeneoMapper
     */
    private $productSeriesTransferAkeneoMapper;

    /**
     * @var int[]
     */
    private $nonTransferredAkeneoProductSeriesIds = [];

    /**
     * @var int
     */
    private $productAkeneoSeriesCountBeforeTransfer = 0;

    /**
     * @param \App\Component\Akeneo\Transfer\AkeneoImportTransferDependency $akeneoImportTransferDependency
     * @param \App\Model\Product\Series\Transfer\Akeneo\ProductSeriesTransferAkeneoFacade $productSeriesTransferAkeneoFacade
     * @param \App\Model\Product\Series\Transfer\Akeneo\ProductSeriesTransferAkeneoValidator $productSeriesTransferAkeneoValidator
     * @param \App\Model\Product\Series\Transfer\Akeneo\ProductSeriesTransferAkeneoMapper $productSeriesTransferAkeneoMapper
     * @param \App\Model\Product\Series\ProductSeriesFacade $productSeriesFacade
     */
    public function __construct(
        AkeneoImportTransferDependency $akeneoImportTransferDependency,
        ProductSeriesTransferAkeneoFacade $productSeriesTransferAkeneoFacade,
        ProductSeriesTransferAkeneoValidator $productSeriesTransferAkeneoValidator,
        ProductSeriesTransferAkeneoMapper $productSeriesTransferAkeneoMapper,
        ProductSeriesFacade $productSeriesFacade
    ) {
        parent::__construct($akeneoImportTransferDependency);
        $this->productSeriesTransferAkeneoFacade = $productSeriesTransferAkeneoFacade;
        $this->productSeriesTransferAkeneoValidator = $productSeriesTransferAkeneoValidator;
        $this->productSeriesFacade = $productSeriesFacade;
        $this->productSeriesTransferAkeneoMapper = $productSeriesTransferAkeneoMapper;
    }

    /**
     * @param array $akeneoData
     */
    protected function processItem($akeneoData): void
    {
        $this->productSeriesTransferAkeneoValidator->validate($akeneoData);
        $productSeries = $this->productSeriesFacade->findByAkeneoCode($akeneoData['code']);

        $productSeriesData = $this->productSeriesTransferAkeneoMapper->mapAkeneoDataToProductSeriesData($akeneoData, $productSeries);

        if ($productSeries === null) {
            $this->logger->addInfo(sprintf('Creating product series with code: %s', $productSeriesData->akeneoCode));
            $productSeries = $this->productSeriesFacade->create($productSeriesData);
        } else {
            $this->logger->addInfo(sprintf('Updating product series with code: %s', $productSeries->getAkeneoCode()));
            $productSeries = $this->productSeriesFacade->edit($productSeries->getId(), $productSeriesData);
        }

        $this->dropTransferredAkeneoProductSeries($productSeries);
    }

    protected function doBeforeTransfer(): void
    {
        $this->loadProductSeriesIds();
    }

    protected function doAfterTransfer(): void
    {
        $this->deleteRestNonTransferredProductSeries();
    }

    private function loadProductSeriesIds(): void
    {
        $allAkeneoProductSeriesIds = $this->productSeriesFacade->getAllAkeneoProductSeriesIds();
        $this->nonTransferredAkeneoProductSeriesIds = array_combine($allAkeneoProductSeriesIds, $allAkeneoProductSeriesIds);
        $this->productAkeneoSeriesCountBeforeTransfer = count($this->nonTransferredAkeneoProductSeriesIds);
    }

    /**
     * @param \App\Model\Product\Series\ProductSeries $productSeries
     */
    private function dropTransferredAkeneoProductSeries(ProductSeries $productSeries): void
    {
        if (array_key_exists($productSeries->getId(), $this->nonTransferredAkeneoProductSeriesIds)) {
            unset($this->nonTransferredAkeneoProductSeriesIds[$productSeries->getId()]);
        }
    }

    private function deleteRestNonTransferredProductSeries(): void
    {
        if ($this->productAkeneoSeriesCountBeforeTransfer > 0 && $this->productAkeneoSeriesCountBeforeTransfer === count($this->nonTransferredAkeneoProductSeriesIds)) {
            $this->logger->addError(sprintf('Import product series from Akeneo probably failed, because all product series with akeneo code should be deleted. Deletion was aborted.'));
            return;
        }

        foreach ($this->nonTransferredAkeneoProductSeriesIds as $productSeriesId) {
            $productSeries = $this->productSeriesFacade->getById($productSeriesId);
            $this->productSeriesFacade->delete($productSeries);
            $this->logger->addWarning(sprintf('Deleted product series with ID: %s', $productSeriesId));
        }
    }

    /**
     * @return \Generator
     */
    protected function getData(): \Generator
    {
        foreach ($this->productSeriesTransferAkeneoFacade->getAllProductSeries() as $akeneoEntityData) {
            yield $akeneoEntityData;
        }
    }

    /**
     * @return string
     */
    public function getTransferName(): string
    {
        return t('přenos seznamu produktových programů');
    }

    /**
     * @return string
     */
    public function getTransferIdentifier(): string
    {
        return 'productSeriesTransfer';
    }
}
