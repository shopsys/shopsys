<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class DummyLabelTranslation
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    public int $id;

    #[ORM\ManyToOne(targetEntity: DummyLabel::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false)]
    public DummyLabel $translatable;

    #[ORM\Column(type: 'string')]
    public string $locale;

    #[ORM\Column(type: 'string')]
    public string $name;
}
