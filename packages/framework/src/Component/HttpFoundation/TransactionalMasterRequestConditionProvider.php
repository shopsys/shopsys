<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\HttpFoundation;

use Override;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class TransactionalMasterRequestConditionProvider implements TransactionalMasterRequestConditionProviderInterface
{
    #[Override]
    public function shouldBeginTransaction(RequestEvent $event): bool
    {
        return $event->isMainRequest();
    }
}
