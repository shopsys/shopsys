<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\OpeningHours\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OpeningHoursNotFoundException extends NotFoundHttpException
{
    public function __construct(int $storeId, int $dayOfWeek)
    {
        parent::__construct(sprintf(
            'Opening hour for store with ID "%d" for "%d" day in week was not found.',
            $storeId,
            $dayOfWeek,
        ));
    }
}
