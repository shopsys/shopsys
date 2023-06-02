<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\PhpStan;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

class GedmoTestEntity
{
    /**
     * @var \Tests\App\Unit\PhpStan\GedmoTestEntity|null
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Tests\App\Unit\PhpStan\GedmoTestEntity", inversedBy="children")
     * @ORM\JoinColumn(nullable=true, name="parent_id", referencedColumnName="id")
     */
    private ?GedmoTestEntity $parent;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection|\Tests\App\Unit\PhpStan\GedmoTestEntity[]
     * @ORM\OneToMany(targetEntity="Tests\App\Unit\PhpStan\GedmoTestEntity", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;

    /**
     * @var int
     * @Gedmo\TreeLevel
     * @ORM\Column(type="integer")
     */
    private int $level;

    /**
     * @var int
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer")
     */
    private int $lft;

    /**
     * @var int
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer")
     */
    private int $rgt;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private string $name;

    /**
     * @return array
     */
    public function returnProperties(): array
    {
        return [
            $this->level,
            $this->lft,
            $this->rgt,
            $this->parent,
            $this->name,
            $this->children,
        ];
    }
}
