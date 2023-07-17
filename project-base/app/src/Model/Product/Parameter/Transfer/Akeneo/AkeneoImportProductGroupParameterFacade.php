<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\AbstractAkeneoImportTransfer;
use App\Component\Akeneo\Transfer\AkeneoImportTransferDependency;
use App\Model\Product\Parameter\ParameterGroupFacade;
use Generator;

class AkeneoImportProductGroupParameterFacade extends AbstractAkeneoImportTransfer
{
    public const PREFIX_PARAMETER_GROUP_CODE = 'param__';

    /**
     * @param \App\Component\Akeneo\Transfer\AkeneoImportTransferDependency $akeneoImportTransferDependency
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\ProductParameterGroupTransferAkeneoFacade $productParameterGroupTransferAkeneoFacade
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\ProductParameterGroupTransferAkeneoMapper $productParameterGroupTransferAkeneoMapper
     * @param \App\Model\Product\Parameter\ParameterGroupFacade $parameterGroupFacade
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\ProductParameterGroupTransferAkeneoValidator $productParameterGroupTransferAkeneoValidator
     */
    public function __construct(
        AkeneoImportTransferDependency $akeneoImportTransferDependency,
        private ProductParameterGroupTransferAkeneoFacade $productParameterGroupTransferAkeneoFacade,
        private ProductParameterGroupTransferAkeneoMapper $productParameterGroupTransferAkeneoMapper,
        private ParameterGroupFacade $parameterGroupFacade,
        private ProductParameterGroupTransferAkeneoValidator $productParameterGroupTransferAkeneoValidator,
    ) {
        parent::__construct($akeneoImportTransferDependency);
    }

    /**
     * {@inheritdoc}
     */
    protected function getData(): Generator
    {
        return $this->productParameterGroupTransferAkeneoFacade->getAllAttributesGroup();
    }

    protected function doBeforeTransfer(): void
    {
        $this->logger->info('Transfer parameter groups data from Akeneo ...');
    }

    /**
     * {@inheritdoc}
     */
    protected function processItem($akeneoParameterGroup): void
    {
        if (strpos($akeneoParameterGroup['code'], self::PREFIX_PARAMETER_GROUP_CODE) === false) {
            return;
        }

        $this->productParameterGroupTransferAkeneoValidator->validate($akeneoParameterGroup);

        $parameterGroup = $this->parameterGroupFacade->findParameterGroupByAkeneoCode($akeneoParameterGroup['code']);
        $parameterGroupData = $this->productParameterGroupTransferAkeneoMapper->mapAkeneoParameterGroupDataToParameterGroupData($akeneoParameterGroup, $parameterGroup);

        if ($parameterGroup === null) {
            $this->logger->info(sprintf('Creating parameter group with akeneo code : %s', $parameterGroupData->akeneoCode));
            $this->parameterGroupFacade->create($parameterGroupData);
        } else {
            $this->logger->info(sprintf('Updating parameter group with akeneo code : %s', $parameterGroup->getAkeneoCode()));
            $this->parameterGroupFacade->edit($parameterGroup->getId(), $parameterGroupData);
        }
    }

    protected function doAfterTransfer(): void
    {
        $this->logger->info('Done');
    }

    /**
     * {@inheritdoc}
     */
    public function getTransferName(): string
    {
        return 'productParameterGroupTransfer';
    }

    /**
     * {@inheritdoc}
     */
    public function getTransferIdentifier(): string
    {
        return t('Products parameters groups transfer');
    }
}
