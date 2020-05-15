<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue as BaseParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData;

/**
 * @ORM\Table(name="parameter_values")
 * @ORM\Entity
 */
class ParameterValue extends BaseParameterValue
{
    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $unit;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $rgbHex;

    /**
     * @param \App\Model\Product\Parameter\ParameterValueData $parameterData
     */
    public function __construct(ParameterValueData $parameterData)
    {
        parent::__construct($parameterData);
        $this->unit = $parameterData->unit;
        $this->rgbHex = $parameterData->rgbHex;
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterValueData $parameterData
     */
    public function edit(ParameterValueData $parameterData)
    {
        parent::edit($parameterData);
        $this->unit = $parameterData->unit;
        $this->rgbHex = $parameterData->rgbHex;
    }

    /**
     * @return string|null
     */
    public function getUnit(): ?string
    {
        return $this->unit;
    }

    /**
     * @return string|null
     */
    public function getRgbHex(): ?string
    {
        return $this->rgbHex;
    }
}
