<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Navigation;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Override;
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
    protected $id;

    /**
     * @var int
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    protected $position;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    protected $url;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $domainId;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $routeName;

    public function __construct(NavigationItemData $navigationItemData)
    {
        $this->setData($navigationItemData);
    }

    public function edit(NavigationItemData $navigationItemData): void
    {
        $this->setData($navigationItemData);
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
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
    public function getDomainId()
    {
        return $this->domainId;
    }

    protected function setData(NavigationItemData $navigationItemData): void
    {
        $this->name = $navigationItemData->name;
        $this->url = $navigationItemData->url;
        $this->domainId = $navigationItemData->domainId;
    }

    /**
     * @param string|null $routeName
     */
    public function setRouteName($routeName): void
    {
        $this->routeName = $routeName;
    }

    /**
     * @return string|null
     */
    public function getRouteName()
    {
        return $this->routeName;
    }
}
