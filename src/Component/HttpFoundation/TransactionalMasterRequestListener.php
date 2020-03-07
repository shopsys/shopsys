<?php

declare(strict_types=1);

namespace App\Component\HttpFoundation;

use Shopsys\FrameworkBundle\Component\HttpFoundation\TransactionalMasterRequestListener as BaseTransactionalMasterRequestListener;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class TransactionalMasterRequestListener extends BaseTransactionalMasterRequestListener
{
    /**
     * @var bool
     */
    private static $transactionHasBeenRollbacked = false;

    public static function setTransactionHasBeenRollbacked(): void
    {
        self::$transactionHasBeenRollbacked = true;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (self::$transactionHasBeenRollbacked === true) {
            return;
        }

        parent::onKernelResponse($event);
    }
}
