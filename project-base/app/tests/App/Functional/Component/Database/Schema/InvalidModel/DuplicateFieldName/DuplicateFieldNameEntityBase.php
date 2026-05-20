<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Database\Schema\InvalidModel\DuplicateFieldName;

use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class DuplicateFieldNameEntityBase
{
    #[ORM\Column(type: 'string', length: 255)]
    protected string $name = '';
}
