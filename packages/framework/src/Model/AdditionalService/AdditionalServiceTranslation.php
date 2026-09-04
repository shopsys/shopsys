<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdditionalService;

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
#[ORM\Table(name: 'additional_service_translations')]
#[ORM\Entity]
class AdditionalServiceTranslation extends AbstractTranslation
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService
     */
    #[AsMcpColumn]
    #[Prezent\Translatable(targetEntity: AdditionalService::class)]
    #[Override]
    protected $translatable;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $name;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    protected $feedName;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 160, nullable: true)]
    protected $zboziDescription;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $description;

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName($name): void
    {
        $this->name = TransformStringHelper::getTrimmedStringOrNullOnEmpty($name);
    }

    /**
     * @return string|null
     */
    public function getFeedName()
    {
        return $this->feedName;
    }

    /**
     * @param string|null $feedName
     */
    public function setFeedName($feedName): void
    {
        $this->feedName = TransformStringHelper::getTrimmedStringOrNullOnEmpty($feedName);
    }

    /**
     * @return string|null
     */
    public function getZboziDescription()
    {
        return $this->zboziDescription;
    }

    /**
     * @param string|null $zboziDescription
     */
    public function setZboziDescription($zboziDescription): void
    {
        $this->zboziDescription = TransformStringHelper::getTrimmedStringOrNullOnEmpty($zboziDescription);
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription($description): void
    {
        $this->description = TransformStringHelper::getTrimmedStringOrNullOnEmpty($description);
    }
}
