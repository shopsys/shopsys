<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class DummyArticle
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    public int $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyArticleTranslation>
     */
    #[ORM\OneToMany(targetEntity: DummyArticleTranslation::class, mappedBy: 'translatable')]
    public Collection $translations;
}
