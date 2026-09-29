<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * A translatable entity reachable only through a to-many association, so that a translated field
 * inside a subquery is covered.
 */
#[ORM\Entity]
final class DummyLabel
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    public int $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyLabelTranslation>
     */
    #[ORM\OneToMany(targetEntity: DummyLabelTranslation::class, mappedBy: 'translatable')]
    public Collection $translations;
}
