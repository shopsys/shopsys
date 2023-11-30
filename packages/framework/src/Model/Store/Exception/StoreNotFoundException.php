<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StoreNotFoundException extends NotFoundHttpException implements StoreException
{
    /**
     * @param int $storeId
     */
    public function __construct(int $storeId)
    {
        parent::__construct(sprintf(
            'Store with ID "%d" was not found.',
            $storeId,
        ));
    }
}
