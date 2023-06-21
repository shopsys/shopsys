<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ExtendedDummyEntity extends DummyEntity
{
    /**
     * Unlike the association in the parent class, this one has the "OrderBy" setting
     *
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]|\Doctrine\Common\Collections\Collection
     * @ORM\ManyToMany(targetEntity="Shopsys\FrameworkBundle\Model\Product\Flag\Flag")
     * @ORM\JoinTable(name="dummy_flags")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    protected array|Collection $flags;
}
