<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Database\Schema\Model;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;

#[ORM\MappedSuperclass]
abstract class ParentAttributedQueryableEntityBase
{
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id = 0;

    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 32)]
    protected string $code = '';
}
