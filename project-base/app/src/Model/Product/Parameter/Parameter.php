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
 * @method \App\Model\Product\Unit\Unit|null getUnit()
 * @property \App\Model\Product\Unit\Unit|null $unit
 */
class Parameter extends BaseParameter
{
    public const AKENEO_ATTRIBUTES_TYPE_BOOLEAN = 'pim_catalog_boolean';
    public const AKENEO_ATTRIBUTES_TYPE_SIMPLE_SELECT = 'pim_catalog_simpleselect';
    public const AKENEO_ATTRIBUTES_TYPE_MULTI_SELECT = 'pim_catalog_multiselect';

    public const COLOR_PARAMETER_AKENEO_CODE = 'param__variant_color';

    /**
     * @var \App\Model\Product\Parameter\ParameterGroup|null
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Parameter\ParameterGroup")
     * @ORM\JoinColumn(nullable=true, name="group_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $group;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, unique=true, nullable=true)
     */
    protected $akeneoCode;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $akeneoType;

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
        $this->akeneoCode = $parameterData->akeneoCode;
        $this->akeneoType = $parameterData->akeneoType;
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
     * @deprecated Visibility of parameters is not used on this project
     */
    public function isVisible()
    {
        return true;
    }
}
