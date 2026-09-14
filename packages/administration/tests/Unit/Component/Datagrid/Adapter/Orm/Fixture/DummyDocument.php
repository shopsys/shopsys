<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class DummyDocument
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    public int $id;

    #[ORM\Column(type: 'guid')]
    public string $uuid;

    #[ORM\Column(type: DummyTextType::NAME)]
    public string $title;

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: 'json')]
    public array $payload;
}
