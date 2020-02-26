<?php

declare(strict_types=1);

namespace App\Model\Product\Flag;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag as BaseFlag;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagData;

/**
 * @property App\Model\Product\Flag\FlagData $flagData
 *
 * @ORM\Table(name="flags")
 * @ORM\Entity
 * @method \App\Model\Product\Flag\FlagTranslation translation(?string $locale = null)
 * @method setTranslations(\App\Model\Product\Flag\FlagData $flagData)
 * @method edit(\App\Model\Product\Flag\FlagData $flagData)
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
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $akeneoCode;

    /**
     * @param \App\Model\Product\Flag\FlagData $flagData
     */
    public function __construct(FlagData $flagData)
    {
        parent::__construct($flagData);
        $this->sale = $flagData->sale;
        $this->akeneoCode = $flagData->akeneoCode ?? '';
    }

    /**
     * @param \App\Model\Product\Flag\FlagData $flagData
     */
    public function edit(FlagData $flagData): void
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

    /**
     * @return string
     */
    public function getAkeneoCode(): ?string
    {
        return $this->akeneoCode;
    }
}
