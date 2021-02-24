<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\Attribute\AkeneoAttributeHelper;
use App\Model\Product\Flag\Flag;
use App\Model\Product\Flag\FlagData;
use App\Model\Product\Flag\FlagDataFactory;

/**
 * @property \App\Model\Product\Flag\Flag $flag
 */
class FlagTransferAkeneoMapper
{
    /**
     * @var \App\Model\Product\Flag\FlagDataFactory
     */
    private $flagDataFactory;

    /**
     * @param \App\Model\Product\Flag\FlagDataFactory $flagDataFactory
     */
    public function __construct(FlagDataFactory $flagDataFactory)
    {
        $this->flagDataFactory = $flagDataFactory;
    }

    /**
     * @param array $akeneoFlagData
     * @param \App\Model\Product\Flag\Flag|null $flag
     * @return \App\Model\Product\Flag\FlagData
     */
    public function mapAkeneoFlagDataToFlagData(array $akeneoFlagData, ?Flag $flag): FlagData
    {
        if ($flag === null) {
            $flagData = $this->flagDataFactory->create();
            $flagData->akeneoCode = $akeneoFlagData['code'];
        } else {
            $flagData = $this->flagDataFactory->createFromFlag($flag);
        }

        $flagData->name = AkeneoAttributeHelper::mapLocalizedDataString($flagData->name, $akeneoFlagData['labels'] ?? null);

        return $flagData;
    }
}
