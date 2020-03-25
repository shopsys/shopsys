<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use App\Model\Product\Parameter\Unit\ParameterUnit;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter as BaseParameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData as BaseParameterData;

/**
 * @ORM\Table(name="parameters")
 * @ORM\Entity
 * @method setTranslations(\App\Model\Product\Parameter\ParameterData $parameterData)
 */
class Parameter extends BaseParameter
{
    /**
     * @var \App\Model\Product\Parameter\ParameterGroup|null
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Parameter\ParameterGroup")
     * @ORM\JoinColumn(nullable=true, name="group_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $group;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=100, unique=true, nullable=true)
     */
    protected $akeneoCode;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $akeneoType;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $orderingPriority;

    /**
     * @var \App\Model\Product\Parameter\Unit\ParameterUnit|null
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Parameter\Unit\ParameterUnit")
     * @ORM\JoinColumn(nullable=true, name="parameter_unit_id", referencedColumnName="id")
     */
    protected $parameterUnit;

    /**
     * Parameter constructor.
     * @param \App\Model\Product\Parameter\ParameterData $parameterData
     */
    public function __construct(BaseParameterData $parameterData)
    {
        parent::__construct($parameterData);
        $this->group = $parameterData->group;
        $this->akeneoCode = $parameterData->akeneoCode;
        $this->akeneoType = $parameterData->akeneoType;
        $this->orderingPriority = $parameterData->orderingPriority;
        $this->parameterUnit = $parameterData->parameterUnit;
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterData $parameterData
     */
    public function edit(BaseParameterData $parameterData)
    {
        parent::edit($parameterData);
        $this->group = $parameterData->group;
        $this->akeneoCode = $parameterData->akeneoCode;
        $this->akeneoType = $parameterData->akeneoType;
        $this->orderingPriority = $parameterData->orderingPriority;
        $this->parameterUnit = $parameterData->parameterUnit;
    }

    /**
     * @return \App\Model\Product\Parameter\ParameterGroup|null
     */
    public function getGroup(): ?ParameterGroup
    {
        return $this->group;
    }

    /**
     * @return string|null
     */
    public function getAkeneoCode(): ?string
    {
        return $this->akeneoCode;
    }

    /**
     * @return string|null
     */
    public function getAkeneoType(): ?string
    {
        return $this->akeneoType;
    }

    /**
     * @return int
     */
    public function getOrderingPriority(): int
    {
        return $this->orderingPriority;
    }

    /**
     * @return \App\Model\Product\Parameter\Unit\ParameterUnit|null
     */
    public function getParameterUnit(): ?ParameterUnit
    {
        return $this->parameterUnit;
    }
}
