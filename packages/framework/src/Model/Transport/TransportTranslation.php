<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Doctrine\ORM\Mapping as ORM;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpInheritedColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[AsMcpInheritedColumn(fieldName: 'id')]
#[AsMcpInheritedColumn(fieldName: 'locale')]
#[ORM\Table(name: 'transport_translations')]
#[ORM\Entity]
class TransportTranslation extends AbstractTranslation
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\Transport
     */
    #[AsMcpColumn]
    #[Prezent\Translatable(targetEntity: Transport::class)]
    #[Override]
    protected $translatable;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $name {
        set {
            $this->name = TransformStringHelper::getTrimmedStringOrNullOnEmpty($value);
        }
    }

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $description {
        set {
            $this->description = TransformStringHelper::getTrimmedStringOrNullOnEmpty($value);
        }
    }

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $instructions {
        set {
            $this->instructions = TransformStringHelper::getTrimmedStringOrNullOnEmpty($value);
        }
    }

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $trackingInstruction {
        set {
            $this->trackingInstruction = TransformStringHelper::getTrimmedStringOrNullOnEmpty($value);
        }
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getInstructions()
    {
        return $this->instructions;
    }

    /**
     * @param string|null $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @param string|null $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @param string|null $instructions
     */
    public function setInstructions($instructions): void
    {
        $this->instructions = $instructions;
    }

    /**
     * @return string|null
     */
    public function getTrackingInstruction()
    {
        return $this->trackingInstruction;
    }

    /**
     * @param string|null $trackingInstruction
     */
    public function setTrackingInstruction($trackingInstruction): void
    {
        $this->trackingInstruction = $trackingInstruction;
    }
}
