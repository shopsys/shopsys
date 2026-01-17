<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\HttpFoundation;

use Symfony\Component\HttpKernel\Event\RequestEvent;

interface TransactionalMasterRequestConditionProviderInterface
{
    public function shouldBeginTransaction(RequestEvent $event): bool;
}
