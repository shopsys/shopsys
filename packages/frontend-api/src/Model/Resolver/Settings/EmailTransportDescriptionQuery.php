<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class EmailTransportDescriptionQuery extends AbstractQuery
{
    public function __construct(
        protected readonly TransportFacade $transportFacade,
    ) {
    }

    public function emailTransportDescriptionQuery(): ?string
    {
        return $this->transportFacade->findEmailTransport()?->getDescription();
    }
}
