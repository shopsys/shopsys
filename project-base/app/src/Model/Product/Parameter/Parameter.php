<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter as BaseParameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData as BaseParameterData;

/**
 * @ORM\Table(name="parameters")
 * @ORM\Entity
 * @method setTranslations(\App\Model\Product\Parameter\ParameterData $parameterData)
 * @method \Shopsys\FrameworkBundle\Model\Product\Unit\Unit|null getUnit()
 * @property \Shopsys\FrameworkBundle\Model\Product\Unit\Unit|null $unit
 */
class Parameter extends BaseParameter
{
    /**
     * @var \App\Model\Product\Parameter\ParameterGroup|null
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Parameter\ParameterGroup")
     * @ORM\JoinColumn(nullable=true, name="group_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $group;

    /**
     * @param \App\Model\Product\Parameter\ParameterData $parameterData
     */
    public function __construct(BaseParameterData $parameterData)
    {
        parent::__construct($parameterData);
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterData $parameterData
     */
    public function edit(BaseParameterData $parameterData)
    {
        parent::edit($parameterData);
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterData $parameterData
     */
    protected function setData(BaseParameterData $parameterData): void
    {
        parent::setData($parameterData);

        // visibility is not used from an extended project, so to ensure everything works as expected, we set it to true
        $this->visible = true;

        $this->group = $parameterData->group;
    }

    /**
     * @return \App\Model\Product\Parameter\ParameterGroup|null
     */
    public function getGroup(): ?ParameterGroup
    {
        return $this->group;
    }

    /**
     * @deprecated Visibility of parameters is not used on this project
     */
    public function isVisible()
    {
        return true;
    }
}
