<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'administrator_pinned_menu_items')]
#[ORM\Entity]
class AdministratorPinnedMenuItem
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    #[ORM\JoinColumn(name: 'administrator_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Administrator::class, inversedBy: 'pinnedMenuItems')]
    protected $administrator;

    /**
     * @var string
     */
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255)]
    protected $routeName;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    protected $position;

    /**
     * @param string $routeName
     * @param int $position
     */
    public function __construct(Administrator $administrator, $routeName, $position)
    {
        $this->administrator = $administrator;
        $this->routeName = $routeName;
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
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
    public function setPosition($position): void
    {
        $this->position = $position;
    }
}
