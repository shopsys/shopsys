<?php

declare(strict_types=1);

namespace App\Model\Product\Unit;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Unit\Unit as BaseUnit;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitData;

/**
 * @ORM\Table(name="units")
 * @ORM\Entity
 * @method __construct(\App\Model\Product\Unit\UnitData $unitData)
 * @method edit(\App\Model\Product\Unit\UnitData $unitData)
 * @method setTranslations(\App\Model\Product\Unit\UnitData $unitData)
 */
class Unit extends BaseUnit
{
    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, unique=true, nullable=true)
     */
    protected ?string $akeneoCode;

    /**
     * @param \App\Model\Product\Unit\UnitData $unitData
     */
    protected function setData(UnitData $unitData): void
    {
        parent::setData($unitData);

        $this->akeneoCode = $unitData->akeneoCode;
    }

    /**
     * @return string|null
     */
    public function getAkeneoCode(): ?string
    {
        return $this->akeneoCode;
    }
}
