<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Database\Schema\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final class QueryableEmbeddable
{
    #[ORM\Column(type: 'string', length: 255)]
    public string $value = '';

    #[ORM\Column(type: 'string', length: 255)]
    public string $noteText = '';
}
