<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueBillingAddressApi extends Constraint
{
    public const DUPLICATE_BILLING_ADDRESS = '9732bc5c-7b8e-404b-ac8c-a1c810f6a045';

    public string $message = 'Billing address company number {{ company_number }} already exists. Contact customer support.';

    /**
     * @var array<string, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected static $errorNames = [
        self::DUPLICATE_BILLING_ADDRESS => 'DUPLICATE_BILLING_ADDRESS',
    ];

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
