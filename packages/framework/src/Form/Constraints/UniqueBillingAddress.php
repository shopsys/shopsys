<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueBillingAddress extends Constraint
{
    public const string DUPLICATE_BILLING_ADDRESS = 'dc6b5879-cb7a-423b-bd97-d9c667d96fd5';

    public string $message = 'Billing address company number {{ company_number }} already exists for domain {{ domain_id }}.';

    public string $errorPath;

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::DUPLICATE_BILLING_ADDRESS => 'DUPLICATE_BILLING_ADDRESS',
    ];
}
