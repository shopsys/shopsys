<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;
use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * @method \Shopsys\FrameworkBundle\Model\Transport\TransportGroupTranslation translation(?string $locale = null)
 * @method \Doctrine\Common\Collections\Collection<string, \Shopsys\FrameworkBundle\Model\Transport\TransportGroupTranslation> getTranslations()
 */
#[AsMcpTable]
#[ORM\Table(name: 'transport_groups')]
#[ORM\Entity]
#[EntityImage]
class TransportGroup extends AbstractTranslatableEntity implements OrderableEntityInterface, Presentable
{
    protected const int GEDMO_SORTABLE_LAST_POSITION = -1;

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
     * @var \Doctrine\Common\Collections\Collection<string, \Shopsys\FrameworkBundle\Model\Transport\TransportGroupTranslation>
     */
    #[Prezent\Translations(targetEntity: TransportGroupTranslation::class)]
    #[Override]
    protected $translations;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[Gedmo\SortablePosition]
    protected $position;

    public function __construct(TransportGroupData $transportGroupData)
    {
        $this->translations = new ArrayCollection();
        $this->position = static::GEDMO_SORTABLE_LAST_POSITION;
        $this->uuid = $transportGroupData->uuid;
        $this->setData($transportGroupData);
    }

    public function edit(TransportGroupData $transportGroupData): void
    {
        $this->setData($transportGroupData);
    }

    protected function setData(TransportGroupData $transportGroupData): void
    {
        $this->setTranslations($transportGroupData);
    }

    protected function setTranslations(TransportGroupData $transportGroupData): void
    {
        foreach ($transportGroupData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportGroupTranslation
     */
    #[Override]
    protected function createTranslation()
    {
        return new TransportGroupTranslation();
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
     * @return string[]
     */
    public function getNames()
    {
        $namesByLocale = [];

        foreach ($this->translations as $translation) {
            $namesByLocale[$translation->getLocale()] = $translation->getName();
        }

        return $namesByLocale;
    }

    /**
     * @param int $position
     */
    #[Override]
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    #[Override]
    public function toHumanReadable(): string
    {
        return $this->getName() ?? (string)$this->getId();
    }
}
