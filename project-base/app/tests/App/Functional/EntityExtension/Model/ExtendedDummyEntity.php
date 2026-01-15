<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;

#[ORM\Entity]
class ExtendedDummyEntity extends DummyEntity
{
    /**
     * Unlike the association in the parent class, this one has the "OrderBy" setting
     *
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Product\Flag\Flag>
     */
    #[ORM\ManyToMany(targetEntity: Flag::class)]
    #[ORM\JoinTable(name: 'dummy_flags')]
    #[ORM\OrderBy(['id' => 'DESC'])]
    protected Collection $flags;
}
