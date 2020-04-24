<?php

declare(strict_types=1);

namespace App\Model\HorizontalMenu;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;

/**
 * @ORM\Table(name="horizontal_menu_items", indexes={@ORM\Index(name="domain_id_idx", columns={"domain_id"})})
 * @ORM\Entity
 */
class HorizontalMenuItem implements OrderableEntityInterface
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $url;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isFurniture;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    private $domainId;

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemData $horizontalMenuItemData
     */
    public function __construct(HorizontalMenuItemData $horizontalMenuItemData)
    {
        $this->name = $horizontalMenuItemData->name;
        $this->url = $horizontalMenuItemData->url;
        $this->isFurniture = $horizontalMenuItemData->isFurniture ?? false;
        $this->domainId = $horizontalMenuItemData->domainId;
    }

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemData $horizontalMenuItemData
     */
    public function edit(HorizontalMenuItemData $horizontalMenuItemData): void
    {
        $this->name = $horizontalMenuItemData->name;
        $this->url = $horizontalMenuItemData->url;
        $this->isFurniture = $horizontalMenuItemData->isFurniture ?? false;
        $this->domainId = $horizontalMenuItemData->domainId;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    /**
     * @return bool
     */
    public function isFurniture(): bool
    {
        return $this->isFurniture;
    }

    /**
     * @return int
     */
    public function getDomainId(): int
    {
        return $this->domainId;
    }
}
