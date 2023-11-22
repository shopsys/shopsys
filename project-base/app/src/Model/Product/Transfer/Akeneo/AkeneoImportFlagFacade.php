<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\AbstractAkeneoImportTransfer;
use App\Component\Akeneo\Transfer\AkeneoImportTransferDependency;
use App\Model\Product\Flag\Flag;
use App\Model\Product\Flag\FlagDataFactory;
use App\Model\Product\Flag\FlagFacade;
use Generator;

/**
 * @property \App\Model\Product\Flag\FlagFacade $flagFacade
 */
class AkeneoImportFlagFacade extends AbstractAkeneoImportTransfer
{
    public const PREFIX_FLAG_CODE = 'flag__';

    /**
     * @var string[]
     */
    protected array $nonImportedFlagCodes = [];

    protected ?int $countBeforeImport = null;

    /**
     * @param \App\Component\Akeneo\Transfer\AkeneoImportTransferDependency $akeneoImportTransferDependency
     * @param \App\Model\Product\Transfer\Akeneo\FlagTransferAkeneoFacade $flagTransferAkeneoFacade
     * @param \App\Model\Product\Transfer\Akeneo\FlagTransferAkeneoValidator $flagTransferAkeneoValidator
     * @param \App\Model\Product\Transfer\Akeneo\FlagTransferAkeneoMapper $flagTransferAkeneoMapper
     * @param \App\Model\Product\Flag\FlagFacade $flagFacade
     * @param \App\Model\Product\Flag\FlagDataFactory $flagDataFactory
     */
    public function __construct(
        AkeneoImportTransferDependency $akeneoImportTransferDependency,
        protected FlagTransferAkeneoFacade $flagTransferAkeneoFacade,
        protected FlagTransferAkeneoValidator $flagTransferAkeneoValidator,
        protected FlagTransferAkeneoMapper $flagTransferAkeneoMapper,
        protected FlagFacade $flagFacade,
        protected FlagDataFactory $flagDataFactory,
    ) {
        parent::__construct($akeneoImportTransferDependency);
    }

    /**
     * @return \Generator
     */
    protected function getData(): Generator
    {
        return $this->flagTransferAkeneoFacade->getAllFlags();
    }

    protected function doBeforeTransfer(): void
    {
        $this->logger->info('Transfer flags data from Akeneo started');
        $this->loadImportedFlagAkeneoCodes();
    }

    /**
     * {@inheritdoc}
     */
    protected function processItem($akeneoFlagData): void
    {
        if (strpos($akeneoFlagData['code'], self::PREFIX_FLAG_CODE) === false) {
            return;
        }

        $this->flagTransferAkeneoValidator->validate($akeneoFlagData);

        $flag = $this->flagFacade->findByAkeneoCode($akeneoFlagData['code']);
        $flagData = $this->flagTransferAkeneoMapper->mapAkeneoFlagDataToFlagData($akeneoFlagData, $flag);

        if ($flag === null) {
            $this->logger->info(sprintf('Creating flag code: %s', $flagData->akeneoCode));
            $this->flagFacade->create($flagData);
        } else {
            $this->logger->info(sprintf('Updating flag code: %s', $flagData->akeneoCode));
            $this->flagFacade->edit($flag->getId(), $flagData);
            $this->dropImportedFlagAkeneo($flag);
        }
    }

    protected function doAfterTransfer(): void
    {
        $this->logger->info('Deleting non-imported flags');
        $this->removeNonTransferedFlags();
    }

    private function loadImportedFlagAkeneoCodes(): void
    {
        $allFlagAkeneoCodes = $this->flagFacade->getAllFlagAkeneoCodes();
        $this->nonImportedFlagCodes = array_combine($allFlagAkeneoCodes, $allFlagAkeneoCodes);
        $this->countBeforeImport = count($allFlagAkeneoCodes);
    }

    /**
     * @param \App\Model\Product\Flag\Flag $flag
     */
    private function dropImportedFlagAkeneo(Flag $flag): void
    {
        if (array_key_exists($flag->getAkeneoCode(), $this->nonImportedFlagCodes)) {
            unset($this->nonImportedFlagCodes[$flag->getAkeneoCode()]);
        }
    }

    private function removeNonTransferedFlags(): void
    {
        if ($this->countBeforeImport === count($this->nonImportedFlagCodes)) {
            $this->logger->info(
                'Import flags from Akeneo probably faild, because all flags with akeneo code should be deleted. '
                . 'Deletion was aborted.',
            );

            return;
        }

        foreach ($this->nonImportedFlagCodes as $code) {
            if ($this->flagFacade->deleteByAkeneoCode($code)) {
                $this->logger->info(sprintf('Deleted flag with code: %s', $code));
            }
        }
    }

    /**
     * @return string
     */
    public function getTransferIdentifier(): string
    {
        return 'productFlagTransfer';
    }

    /**
     * @return string
     */
    public function getTransferName(): string
    {
        return t('Products flags transfer');
    }
}
