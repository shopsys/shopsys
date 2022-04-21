<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use Symfony\Component\Validator\Constraint;

class PaymentInCart extends Constraint
{
    public const UNAVAILABLE_PAYMENT_ERROR = '49287486-fd4a-40e7-b64a-e45fd60b3732';

    public string $unavailablePaymentMessage = 'Payment with provided UUID is not available';

    /**
     * @var array<string, string>
     */
    protected static $errorNames = [
        self::UNAVAILABLE_PAYMENT_ERROR => 'UNAVAILABLE_PAYMENT_ERROR',
    ];

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
