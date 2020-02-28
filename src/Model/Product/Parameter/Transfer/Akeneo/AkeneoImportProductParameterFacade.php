<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\AbstractAkeneoImportTransfer;
use App\Component\Akeneo\Transfer\AkeneoImportTransferDependency;
use App\Model\Product\Parameter\ParameterFacade;

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
     * @param \App\Component\Akeneo\Transfer\AkeneoImportTransferDependency $akeneoImportTransferDependency
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\ProductParameterTransferAkeneoFacade $productParameterTransferAkeneoFacade
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\ProductParameterTransferAkeneoMapper $productParameterTransferAkeneoMapper
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\ProductParameterTransferAkeneoValidator $productParameterTransferAkeneoValidator
     */
    public function __construct(
        AkeneoImportTransferDependency $akeneoImportTransferDependency,
        ProductParameterTransferAkeneoFacade $productParameterTransferAkeneoFacade,
        ProductParameterTransferAkeneoMapper $productParameterTransferAkeneoMapper,
        ParameterFacade $parameterFacade,
        ProductParameterTransferAkeneoValidator $productParameterTransferAkeneoValidator
    ) {
        parent::__construct($akeneoImportTransferDependency);

        $this->productParameterTransferAkeneoFacade = $productParameterTransferAkeneoFacade;
        $this->parameterFacade = $parameterFacade;
        $this->productParameterTransferAkeneoMapper = $productParameterTransferAkeneoMapper;
        $this->productParameterTransferAkeneoValidator = $productParameterTransferAkeneoValidator;
    }

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

        if ($parameter === null) {
            $this->logger->addInfo(sprintf('Creating parameter group with akeneo code : %s', $parameterData->akeneoCode));
            $this->parameterFacade->create($parameterData);
        } else {
            $this->logger->addInfo(sprintf('Updating parameter group with akeneo code : %s', $parameter->getAkeneoCode()));
            $this->parameterFacade->edit($parameter->getId(), $parameterData);
        }
    }

    protected function doAfterTransfer(): void
    {
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
}
