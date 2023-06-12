<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\PhpStan;

use Doctrine\Common\Collections\ArrayCollection;
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
    private ArrayCollection|array $children;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(type="integer")
     */
    private int $level;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer")
     */
    private int $lft;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer")
     */
    private int $rgt;

    /**
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
