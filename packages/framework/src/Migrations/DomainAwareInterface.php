<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

interface DomainAwareInterface
{
    public function setDomain(Domain $domain): void;
}
