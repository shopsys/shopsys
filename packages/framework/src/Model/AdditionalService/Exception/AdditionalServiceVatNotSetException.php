<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdditionalService\Exception;

use Exception;

class AdditionalServiceVatNotSetException extends Exception
{
    public function __construct(int $additionalServiceId, int $domainId, ?Exception $previous = null)
    {
        $message = sprintf(
            'Additional service with ID %d does not use the product VAT rate on domain ID %d but has no own VAT rate set.',
            $additionalServiceId,
            $domainId,
        );

        parent::__construct($message, 0, $previous);
    }
}
