<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue as BaseParameterValue;

/**
 * @ORM\Table(name="parameter_values")
 * @ORM\Entity
 * @method __construct(\App\Model\Product\Parameter\ParameterValueData $parameterData)
 * @method edit(\App\Model\Product\Parameter\ParameterValueData $parameterData)
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
     * @return string|null
     */
    public function getUnit(): ?string
    {
        return $this->unit;
    }
}
