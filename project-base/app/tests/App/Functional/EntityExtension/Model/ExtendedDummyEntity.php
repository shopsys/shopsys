<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use SortDirection;

#[ORM\Entity]
class ExtendedDummyEntity extends DummyEntity
{
    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Product\Flag\Flag>
     */
    #[ORM\ManyToMany(targetEntity: Flag::class)]
    #[ORM\JoinTable(name: 'dummy_flags')]
    #[ORM\OrderBy(['id' => SortDirection::Descending])]
    #[Override]
    protected Collection $flags;
}
