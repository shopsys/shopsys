<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Shopsys\FrameworkBundle\Component\Cdn\CdnFacade;

interface CdnAwareInterface
{
    public function setCdnFacade(CdnFacade $cdnFacade): void;
}
