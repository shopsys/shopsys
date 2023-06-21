<?php

declare(strict_types=1);

namespace App\Model\Navigation;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;

/**
 * @ORM\Table(name="navigation_items", indexes={@ORM\Index(name="domain_id_idx", columns={"domain_id"})})
 * @ORM\Entity
 */
class NavigationItem implements OrderableEntityInterface
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    private $url;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    private $domainId;

    /**
     * @param \App\Model\Navigation\NavigationItemData $navigationItemData
     */
    public function __construct(NavigationItemData $navigationItemData)
    {
        $this->name = $navigationItemData->name;
        $this->url = $navigationItemData->url;
        $this->domainId = $navigationItemData->domainId;
    }

    /**
     * @param \App\Model\Navigation\NavigationItemData $navigationItemData
     */
    public function edit(NavigationItemData $navigationItemData): void
    {
        $this->name = $navigationItemData->name;
        $this->url = $navigationItemData->url;
        $this->domainId = $navigationItemData->domainId;
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
     * @return int
     */
    public function getDomainId(): int
    {
        return $this->domainId;
    }
}
