<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\AbstractAkeneoImportTransfer;
use App\Component\Akeneo\Transfer\AkeneoImportTransferDependency;
use App\Model\Product\Parameter\ParameterFacade;
use App\Model\Product\Parameter\Unit\ParameterUnit;
use App\Model\Product\Parameter\Unit\ParameterUnitDataFactory;
use App\Model\Product\Parameter\Unit\ParameterUnitFacade;

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
     * @var \App\Model\Product\Parameter\Unit\ParameterUnitFacade
     */
    private $parameterUnitFacade;

    /**
     * @var \App\Model\Product\Parameter\Unit\ParameterUnitDataFactory
     */
    private $parameterUnitDataFactory;

    /**
     * @var int[]
     */
    private $notTransferredParameterIds = [];

    /**
     * @var int
     */
    private $parametersFromAkeneoCountBeforeTransfer;

    /**
     * @param \App\Component\Akeneo\Transfer\AkeneoImportTransferDependency $akeneoImportTransferDependency
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\ProductParameterTransferAkeneoFacade $productParameterTransferAkeneoFacade
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\ProductParameterTransferAkeneoMapper $productParameterTransferAkeneoMapper
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\ProductParameterTransferAkeneoValidator $productParameterTransferAkeneoValidator
     * @param \App\Model\Product\Parameter\Unit\ParameterUnitFacade $parameterUnitFacade
     * @param \App\Model\Product\Parameter\Unit\ParameterUnitDataFactory $parameterUnitDataFactory
     */
    public function __construct(
        AkeneoImportTransferDependency $akeneoImportTransferDependency,
        ProductParameterTransferAkeneoFacade $productParameterTransferAkeneoFacade,
        ProductParameterTransferAkeneoMapper $productParameterTransferAkeneoMapper,
        ParameterFacade $parameterFacade,
        ProductParameterTransferAkeneoValidator $productParameterTransferAkeneoValidator,
        ParameterUnitFacade $parameterUnitFacade,
        ParameterUnitDataFactory $parameterUnitDataFactory
    ) {
        parent::__construct($akeneoImportTransferDependency);

        $this->productParameterTransferAkeneoFacade = $productParameterTransferAkeneoFacade;
        $this->parameterFacade = $parameterFacade;
        $this->productParameterTransferAkeneoMapper = $productParameterTransferAkeneoMapper;
        $this->productParameterTransferAkeneoValidator = $productParameterTransferAkeneoValidator;
        $this->parameterUnitFacade = $parameterUnitFacade;
        $this->parameterUnitDataFactory = $parameterUnitDataFactory;
    }

    public const DEFAULT_METRIC_UNIT_AKENEO_KEY = 'default_metric_unit';

    /**
     * @inheritDoc
     */
    protected function getData(): \Generator
    {
        return $this->productParameterTransferAkeneoFacade->getAllAttributes();
    }

    protected function doBeforeTransfer(): void
    {
        $this->logger->addInfo('Transfer parameters data from Akeneo ...');
        $this->loadAkeneoParameterIds();
    }

    /**
     * @inheritDoc
     */
    protected function processItem($akeneoParameterData): void
    {
        if (strpos($akeneoParameterData['code'], self::PREFIX_PARAMETER_CODE) === false) {
            return;
        }

        $this->productParameterTransferAkeneoValidator->validate($akeneoParameterData);

        $parameter = $this->parameterFacade->findParameterByAkeneoCode($akeneoParameterData['code']);
        $parameterData = $this->productParameterTransferAkeneoMapper->mapAkeneoParameterDataToParameterData($akeneoParameterData, $parameter);
        $parameterData->parameterUnit = $this->saveParameterUnit($akeneoParameterData[self::DEFAULT_METRIC_UNIT_AKENEO_KEY] ?? null);

        if ($parameter === null) {
            $this->logger->addInfo(sprintf('Creating parameter group with akeneo code : %s', $parameterData->akeneoCode));
            $this->parameterFacade->create($parameterData);
        } else {
            $this->logger->addInfo(sprintf('Updating parameter group with akeneo code : %s', $parameter->getAkeneoCode()));
            $this->parameterFacade->edit($parameter->getId(), $parameterData);
            $this->dropParameterFromNotTransferredParameterIds($parameter->getId());
        }
    }

    protected function doAfterTransfer(): void
    {
        $this->deleteNonTransferredParameters();

        $this->logger->addInfo('Done');
    }

    /**
     * @inheritDoc
     */
    public function getTransferName(): string
    {
        return 'productParameterTransfer';
    }

    /**
     * @inheritDoc
     */
    public function getTransferIdentifier(): string
    {
        return t('Přenos parametrů produkt');
    }

    /**
     * @param string|null $akeneoParameterDefaultMetricUnitCode
     * @return \App\Model\Product\Parameter\Unit\ParameterUnit|null
     */
    private function saveParameterUnit(?string $akeneoParameterDefaultMetricUnitCode): ?ParameterUnit
    {
        if ($akeneoParameterDefaultMetricUnitCode === null) {
            return null;
        }

        $parameterUnit = $this->parameterUnitFacade->findByAkeneoCode($akeneoParameterDefaultMetricUnitCode);

        if ($parameterUnit === null) {
            $this->logger->addInfo(sprintf('Creating parameter unit : %s', $akeneoParameterDefaultMetricUnitCode));

            $parameterUnitData = $this->parameterUnitDataFactory->create();
            $parameterUnitData->akeneoCode = $akeneoParameterDefaultMetricUnitCode;
            $parameterUnit = $this->parameterUnitFacade->create($parameterUnitData);
        }

        return $parameterUnit;
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
            $this->logger->addError('Import parameters from Akeneo probably failed, all parameters with akeneo code should be deleted. Deletion was aborted.');
            return;
        }

        foreach ($this->notTransferredParameterIds as $parameterId) {
            $this->parameterFacade->deleteById($parameterId);
            $this->logger->addWarning(sprintf('Deleted parameter with ID: %s', $parameterId));
        }
    }
}
