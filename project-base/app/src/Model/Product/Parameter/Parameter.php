<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use App\Model\Product\Parameter\Exception\DeprecatedParameterPropertyException;
use App\Model\Product\Unit\Unit;
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
    public const AKENEO_ATTRIBUTES_TYPE_BOOLEAN = 'pim_catalog_boolean';
    public const AKENEO_ATTRIBUTES_TYPE_SIMPLE_SELECT = 'pim_catalog_simpleselect';
    public const AKENEO_ATTRIBUTES_TYPE_MULTI_SELECT = 'pim_catalog_multiselect';

    public const COLOR_PARAMETER_AKENEO_CODE = 'param__variant_color';

    public const PARAMETER_TYPE_COMMON = 'checkbox';

    public const PARAMETER_TYPE_SLIDER = 'slider';
    public const PARAMETER_TYPE_COLOR = 'colorPicker';
    public const PARAMETER_TYPES = [
        self::PARAMETER_TYPE_COMMON => self::PARAMETER_TYPE_COMMON,
        self::PARAMETER_TYPE_SLIDER => self::PARAMETER_TYPE_SLIDER,
        self::PARAMETER_TYPE_COLOR => self::PARAMETER_TYPE_COLOR,
    ];

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
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $orderingPriority;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    protected $parameterType;

    /**
     * @var \App\Model\Product\Unit\Unit|null
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Unit\Unit")
     * @ORM\JoinColumn(nullable=true, name="unit_id", referencedColumnName="id")
     */
    protected ?Unit $unit;

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
    public function edit(BaseParameterData $parameterData): void
    {
        parent::edit($parameterData);
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterData $parameterData
     */
    protected function setData(BaseParameterData $parameterData): void
    {
        // parent method not called intentionally to avoid setting parameter visibility
        $this->setTranslations($parameterData);

        $this->group = $parameterData->group;
        $this->akeneoCode = $parameterData->akeneoCode;
        $this->akeneoType = $parameterData->akeneoType;
        $this->orderingPriority = $parameterData->orderingPriority;
        $this->parameterType = $parameterData->parameterType;
        $this->unit = $parameterData->unit;
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
     * @return \App\Model\Product\Unit\Unit|null
     */
    public function getUnit(): ?Unit
    {
        return $this->unit;
    }

    /**
     * @throws \App\Model\Product\Parameter\Exception\DeprecatedParameterPropertyException
     * @deprecated Visibility of parameters is not used on this project
     */
    public function isVisible(): bool
    {
        throw new DeprecatedParameterPropertyException('isVisible');
    }

    /**
     * @return string
     */
    public function getParameterType(): string
    {
        return $this->parameterType;
    }

    /**
     * @return bool
     */
    public function isSlider(): bool
    {
        return $this->getParameterType() === self::PARAMETER_TYPE_SLIDER;
    }
}
