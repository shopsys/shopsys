<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\AbstractAkeneoImportTransfer;
use App\Component\Akeneo\Transfer\AkeneoImportTransferDependency;
use App\Model\Product\Parameter\ParameterFacade;
use App\Model\Product\Unit\Unit;
use App\Model\Product\Unit\UnitDataFactory;
use App\Model\Product\Unit\UnitFacade;
use Generator;

class AkeneoImportProductParameterFacade extends AbstractAkeneoImportTransfer
{
    public const PREFIX_PARAMETER_CODE = 'param__';

    /**
     * @var \App\Model\Product\Parameter\Transfer\Akeneo\ProductParameterTransferAkeneoFacade
     */
    private $productParameterTransferAkeneoFacade;

    /**
     * @var \App\Model\Product\Parameter\ParameterFacade
     */
    private $parameterFacade;

    /**
     * @var \App\Model\Product\Parameter\Transfer\Akeneo\ProductParameterTransferAkeneoMapper
     */
    private $productParameterTransferAkeneoMapper;

    /**
     * @var \App\Model\Product\Parameter\Transfer\Akeneo\ProductParameterTransferAkeneoValidator
     */
    private $productParameterTransferAkeneoValidator;

    /**
     * @var int[]
     */
    private $notTransferredParameterIds = [];

    /**
     * @var int
     */
    private $parametersFromAkeneoCountBeforeTransfer;

    /**
     * @var \App\Model\Product\Unit\UnitFacade
     */
    private UnitFacade $unitFacade;

    /**
     * @var \App\Model\Product\Unit\UnitDataFactory
     */
    private UnitDataFactory $unitDataFactory;

    /**
     * @param \App\Component\Akeneo\Transfer\AkeneoImportTransferDependency $akeneoImportTransferDependency
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\ProductParameterTransferAkeneoFacade $productParameterTransferAkeneoFacade
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\ProductParameterTransferAkeneoMapper $productParameterTransferAkeneoMapper
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\ProductParameterTransferAkeneoValidator $productParameterTransferAkeneoValidator
     * @param \App\Model\Product\Unit\UnitFacade $unitFacade
     * @param \App\Model\Product\Unit\UnitDataFactory $unitDataFactory
     */
    public function __construct(
        AkeneoImportTransferDependency $akeneoImportTransferDependency,
        ProductParameterTransferAkeneoFacade $productParameterTransferAkeneoFacade,
        ProductParameterTransferAkeneoMapper $productParameterTransferAkeneoMapper,
        ParameterFacade $parameterFacade,
        ProductParameterTransferAkeneoValidator $productParameterTransferAkeneoValidator,
        UnitFacade $unitFacade,
        UnitDataFactory $unitDataFactory
    ) {
        parent::__construct($akeneoImportTransferDependency);

        $this->productParameterTransferAkeneoFacade = $productParameterTransferAkeneoFacade;
        $this->parameterFacade = $parameterFacade;
        $this->productParameterTransferAkeneoMapper = $productParameterTransferAkeneoMapper;
        $this->productParameterTransferAkeneoValidator = $productParameterTransferAkeneoValidator;
        $this->unitFacade = $unitFacade;
        $this->unitDataFactory = $unitDataFactory;
    }

    public const DEFAULT_METRIC_UNIT_AKENEO_KEY = 'default_metric_unit';

    /**
     * {@inheritdoc}
     */
    protected function getData(): Generator
    {
        return $this->productParameterTransferAkeneoFacade->getAllAttributes();
    }

    protected function doBeforeTransfer(): void
    {
        $this->logger->info('Transfer parameters data from Akeneo ...');
        $this->loadAkeneoParameterIds();
    }

    /**
     * {@inheritdoc}
     */
    protected function processItem($akeneoParameterData): void
    {
        if (strpos($akeneoParameterData['code'], self::PREFIX_PARAMETER_CODE) === false) {
            return;
        }

        $this->productParameterTransferAkeneoValidator->validate($akeneoParameterData);

        $parameter = $this->parameterFacade->findParameterByAkeneoCode($akeneoParameterData['code']);
        $parameterData = $this->productParameterTransferAkeneoMapper->mapAkeneoParameterDataToParameterData($akeneoParameterData, $parameter);

        if (array_key_exists(self::DEFAULT_METRIC_UNIT_AKENEO_KEY, $akeneoParameterData)) {
            $parameterData->unit = $this->saveUnit($akeneoParameterData[self::DEFAULT_METRIC_UNIT_AKENEO_KEY]);
        } else {
            $parameterData->unit = null;
        }

        if ($parameter === null) {
            $this->logger->info(sprintf('Creating parameter group with akeneo code : %s', $parameterData->akeneoCode));
            $this->parameterFacade->create($parameterData);
        } else {
            $this->logger->info(sprintf('Updating parameter group with akeneo code : %s', $parameter->getAkeneoCode()));
            $this->parameterFacade->edit($parameter->getId(), $parameterData);
            $this->dropParameterFromNotTransferredParameterIds($parameter->getId());
        }
    }

    protected function doAfterTransfer(): void
    {
        $this->deleteNonTransferredParameters();

        $this->logger->info('Done');
    }

    /**
     * {@inheritdoc}
     */
    public function getTransferName(): string
    {
        return 'productParameterTransfer';
    }

    /**
     * {@inheritdoc}
     */
    public function getTransferIdentifier(): string
    {
        return t('Přenos parametrů produkt');
    }

    /**
     * @param string $akeneoParameterDefaultMetricUnitCode
     * @return \App\Model\Product\Unit\Unit
     */
    private function saveUnit(string $akeneoParameterDefaultMetricUnitCode): Unit
    {
        $unit = $this->unitFacade->findByAkeneoCode($akeneoParameterDefaultMetricUnitCode);

        if ($unit === null) {
            $this->logger->info(sprintf('Creating unit : %s', $akeneoParameterDefaultMetricUnitCode));

            $unitData = $this->unitDataFactory->create();
            $unitData->akeneoCode = $akeneoParameterDefaultMetricUnitCode;
            $unit = $this->unitFacade->create($unitData);
        }

        return $unit;
    }

    private function loadAkeneoParameterIds(): void
    {
        $allAkeneoParameterIds = $this->parameterFacade->getAllAkeneoParameterIds();
        $this->notTransferredParameterIds = array_combine($allAkeneoParameterIds, $allAkeneoParameterIds);
        $this->parametersFromAkeneoCountBeforeTransfer = count($this->notTransferredParameterIds);
    }

    /**
     * @param int $parameterId
     */
    private function dropParameterFromNotTransferredParameterIds(int $parameterId): void
    {
        unset($this->notTransferredParameterIds[$parameterId]);
    }

    private function deleteNonTransferredParameters(): void
    {
        if ($this->parametersFromAkeneoCountBeforeTransfer === count($this->notTransferredParameterIds)) {
            $this->logger->error('Import parameters from Akeneo probably failed, all parameters with akeneo code should be deleted. Deletion was aborted.');
            return;
        }

        foreach ($this->notTransferredParameterIds as $parameterId) {
            $this->parameterFacade->deleteById($parameterId);
            $this->logger->warning(sprintf('Deleted parameter with ID: %s', $parameterId));
        }
    }
}
