<?php

declare(strict_types=1);

namespace App\Model\Product\Flag;

use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagData as BaseFlagData;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagDataFactory as BaseFlagDataFactory;

/**
 * @method fillNew(\App\Model\Product\Flag\FlagData $flagData)
 * @property \App\Model\Product\Flag\Flag $flag
 * @property \App\Model\Product\Flag\FlagData $flagData
 * @property \App\Component\Domain\Domain $domain
 * @method __construct(\App\Component\Domain\Domain $domain)
 */
class FlagDataFactory extends BaseFlagDataFactory
{
    /**
     * @return \App\Model\Product\Flag\FlagData
     */
    protected function createInstance(): BaseFlagData
    {
        return new FlagData();
    }

    /**
     * @param \App\Model\Product\Flag\FlagData $flagData
     * @param \App\Model\Product\Flag\Flag $flag
     */
    protected function fillFromFlag(BaseFlagData $flagData, Flag $flag): void
    {
        parent::fillFromFlag($flagData, $flag);

        $flagData->sale = $flag->isSale();
        $flagData->akeneoCode = $flag->getAkeneoCode();
        $flagData->noticeLowPrice = $flag->getNoticeLowPrice();
        $flagData->noticeHighPrice = $flag->getNoticeHighPrice();
    }

    /**
     * @param \App\Model\Product\Flag\Flag $flag
     * @return \App\Model\Product\Flag\FlagData
     */
    public function createFromFlag(Flag $flag): BaseFlagData
    {
        $flagData = new FlagData();
        $this->fillFromFlag($flagData, $flag);

        return $flagData;
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
