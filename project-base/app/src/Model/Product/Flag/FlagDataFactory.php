<?php

declare(strict_types=1);

namespace App\Model\Product\Flag;

use Override;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagData as BaseFlagData;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagDataFactory as BaseFlagDataFactory;

/**
 * @method fillNew(\App\Model\Product\Flag\FlagData $flagData)
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @method \App\Model\Product\Flag\FlagData create()
 * @method \App\Model\Product\Flag\FlagData createFromFlag(\App\Model\Product\Flag\Flag $flag)
 * @method fillFromFlag(\App\Model\Product\Flag\FlagData $flagData, \App\Model\Product\Flag\Flag $flag)
 */
class FlagDataFactory extends BaseFlagDataFactory
{
    /**
     * @return \App\Model\Product\Flag\FlagData
     */
    #[Override]
    protected function createInstance(): BaseFlagData
    {
        return new FlagData();
    }
}
