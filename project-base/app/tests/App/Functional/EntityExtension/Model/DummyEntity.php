<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;

#[ORM\Entity]
class DummyEntity
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Product\Flag\Flag>
     */
    #[ORM\ManyToMany(targetEntity: Flag::class)]
    #[ORM\JoinTable(name: 'dummy_flags')]
    protected Collection $flags;
}
