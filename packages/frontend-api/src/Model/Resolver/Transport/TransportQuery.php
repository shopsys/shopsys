<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Transport;

use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class TransportQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly TransportFacade $transportFacade,
        protected readonly Domain $domain
    ) {
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport
     */
    public function transportByTransportUuidQuery(string $uuid): Transport
    {
        try {
            return $this->transportFacade->getEnabledOnDomainByUuid($uuid, $this->domain->getId());
        } catch (TransportNotFoundException $transportNotFoundException) {
            throw new UserError($transportNotFoundException->getMessage());
        }
    }
}
