<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An entity whose own field has the same name as a field of its translation.
 */
#[ORM\Entity]
final class DummyShadowedArticle
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    public int $id;

    #[ORM\Column(type: 'string')]
    public string $name;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyShadowedArticleTranslation>
     */
    #[ORM\OneToMany(targetEntity: DummyShadowedArticleTranslation::class, mappedBy: 'translatable')]
    public Collection $translations;
}
