<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Shopsys\FrameworkBundle\Model\Customer\BillingAddress;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueBillingAddress extends Constraint
{
    public const DUPLICATE_BILLING_ADDRESS = 'dc6b5879-cb7a-423b-bd97-d9c667d96fd5';

    public string $message = 'Billing address company number {{ company_number }} already exists for domain {{ domain_id }}.';

    public string $errorPath;

    /**
     * @var array<string, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected static $errorNames = [
        self::DUPLICATE_BILLING_ADDRESS => 'DUPLICATE_BILLING_ADDRESS',
    ];
}
