<?php

declare(strict_types=1);

namespace App\Model\Product\Flag;

use Shopsys\FrameworkBundle\Model\Product\Flag\Flag as BaseFlag;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagData as BaseFlagData;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagDataFactory as BaseFlagDataFactory;

/**
 * @method fillNew(\App\Model\Product\Flag\FlagData $flagData)
 * @method \App\Model\Product\Flag\FlagData createFromFlag(\App\Model\Product\Flag\Flag $flag)
 */
class FlagDataFactory extends BaseFlagDataFactory
{
    /**
     * @param \App\Model\Product\Flag\FlagData $flagData
     * @param \App\Model\Product\Flag\Flag $flag
     */
    protected function fillFromFlag(BaseFlagData $flagData, BaseFlag $flag): void
    {
        parent::fillFromFlag($flagData, $flag);
        $flagData->sale = $flag->isSale();
    }

    /**
     * @return \App\Model\Product\Flag\FlagData
     */
    public function create(): BaseFlagData
    {
        $flagData = new FlagData();
        $this->fillNew($flagData);
        return $flagData;
    }
}
