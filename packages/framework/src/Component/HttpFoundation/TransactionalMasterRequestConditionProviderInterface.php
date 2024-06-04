<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\HttpFoundation;

use Symfony\Component\HttpKernel\Event\RequestEvent;

interface TransactionalMasterRequestConditionProviderInterface
{
    /**
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     * @return bool
     */
    public function shouldBeginTransaction(RequestEvent $event): bool;
}
