<?php

declare(strict_types=1);

namespace App\Model\Product\Flag;

use Doctrine\ORM\Mapping as ORM;
use Override;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag as BaseFlag;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagData;

/**
 * @property \App\Model\Product\Flag\FlagData $flagData
 * @ORM\Table(name="flags")
 * @ORM\Entity
 * @method setTranslations(\App\Model\Product\Flag\FlagData $flagData)
 * @method __construct(\App\Model\Product\Flag\FlagData $flagData)
 */
class Flag extends BaseFlag
{
    /**
     * @param \App\Model\Product\Flag\FlagData $flagData
     */
    #[Override]
    public function edit(FlagData $flagData): void
    {
        parent::edit($flagData);
    }

    /**
     * @param \App\Model\Product\Flag\FlagData $flagData
     */
    #[Override]
    public function setData(FlagData $flagData): void
    {
        parent::setData($flagData);
    }
}
