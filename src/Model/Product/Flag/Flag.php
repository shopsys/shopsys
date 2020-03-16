<?php

declare(strict_types=1);

namespace App\Model\Product\Flag;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag as BaseFlag;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagData as BaseFlagData;

/**
 * @ORM\Table(name="flags")
 * @ORM\Entity
 *
 * @method \App\Model\Product\Flag\FlagTranslation translation(?string $locale = null)
 * @method setTranslations(\App\Model\Product\Flag\FlagData $flagData)
 */
class Flag extends BaseFlag
{
    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default"=false})
     */
    protected $sale;

    /**
     * @param \App\Model\Product\Flag\FlagData $flagData
     */
    public function __construct(FlagData $flagData)
    {
        parent::__construct($flagData);
        $this->sale = $flagData->sale;
    }

    /**
     * @param \App\Model\Product\Flag\FlagData $flagData
     */
    public function edit(BaseFlagData $flagData): void
    {
        parent::edit($flagData);
        $this->sale = $flagData->sale;
    }

    /**
     * @return bool
     */
    public function isSale(): bool
    {
        return $this->sale;
    }
}
