<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TransportGroupNotFoundException extends NotFoundHttpException
{
    public function __construct(int $transportGroupId)
    {
        parent::__construct(sprintf('Transport group with ID %d not found.', $transportGroupId));
    }
}
