<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;
use Shopsys\FrameworkBundle\Model\Product\Unit\Unit;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterTranslation translation(?string $locale = null)
 * @method \Doctrine\Common\Collections\Collection<string, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterTranslation> getTranslations()
 */
#[AsMcpTable]
#[ORM\Table(name: 'parameters')]
#[ORM\Entity]
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
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Override]
    protected $id;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid {
        set {
            $this->uuid = $value ?: Uuid::uuid4()->toString();
        }
    }

    /**
     * @var \Doctrine\Common\Collections\Collection<string, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterTranslation>
     */
    #[Prezent\Translations(targetEntity: ParameterTranslation::class)]
    #[Override]
    protected $translations;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100, nullable: false)]
    protected $parameterType;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $orderingPriority;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Unit\Unit|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: true, name: 'unit_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Unit::class)]
    protected $unit;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: true, name: 'group_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: ParameterGroup::class)]
    protected $group;

    public function __construct(ParameterData $parameterData)
    {
        $this->translations = new ArrayCollection();
        $this->uuid = $parameterData->uuid;
        $this->setData($parameterData);
    }

    public function edit(ParameterData $parameterData): void
    {
        $this->setData($parameterData);
    }

    protected function setData(ParameterData $parameterData): void
    {
        $this->setTranslations($parameterData);
        $this->orderingPriority = $parameterData->orderingPriority;
        $this->parameterType = $parameterData->parameterType;
        $this->unit = $parameterData->unit;
        $this->group = $parameterData->group;
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

    protected function setTranslations(ParameterData $parameterData): void
    {
        foreach ($parameterData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterTranslation
     */
    #[Override]
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

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup|null
     */
    public function getGroup()
    {
        return $this->group;
    }
}
