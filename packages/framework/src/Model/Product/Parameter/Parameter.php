<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="parameters")
 * @ORM\Entity
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterTranslation translation(?string $locale = null)
 */
class Parameter extends AbstractTranslatableEntity
{
    public const string PARAMETER_TYPE_COMMON = 'checkbox';
    public const string PARAMETER_TYPE_SLIDER = 'slider';
    public const string PARAMETER_TYPE_COLOR = 'colorPicker';
    public const array PARAMETER_TYPES = [
        self::PARAMETER_TYPE_COMMON => self::PARAMETER_TYPE_COMMON,
        self::PARAMETER_TYPE_SLIDER => self::PARAMETER_TYPE_SLIDER,
        self::PARAMETER_TYPE_COLOR => self::PARAMETER_TYPE_COLOR,
    ];

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    protected $uuid;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterTranslation>
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterTranslation")
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $translations;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $visible;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    protected $parameterType;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $orderingPriority;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Unit\Unit|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Unit\Unit")
     * @ORM\JoinColumn(nullable=true, name="unit_id", referencedColumnName="id")
     */
    protected $unit;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData $parameterData
     */
    public function __construct(ParameterData $parameterData)
    {
        $this->translations = new ArrayCollection();
        $this->uuid = $parameterData->uuid ?: Uuid::uuid4()->toString();
        $this->setData($parameterData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData $parameterData
     */
    public function edit(ParameterData $parameterData)
    {
        $this->setData($parameterData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData $parameterData
     */
    protected function setData(ParameterData $parameterData): void
    {
        $this->setTranslations($parameterData);
        $this->visible = $parameterData->visible;
        $this->orderingPriority = $parameterData->orderingPriority;
        $this->parameterType = $parameterData->parameterType;
        $this->unit = $parameterData->unit;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getName($locale = null)
    {
        return $this->translation($locale)->getName();
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData $parameterData
     */
    protected function setTranslations(ParameterData $parameterData)
    {
        foreach ($parameterData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterTranslation
     */
    protected function createTranslation()
    {
        return new ParameterTranslation();
    }

    /**
     * @return string
     */
    public function getParameterType()
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

    /**
     * @return int
     */
    public function getOrderingPriority()
    {
        return $this->orderingPriority;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Unit\Unit|null
     */
    public function getUnit()
    {
        return $this->unit;
    }
}
