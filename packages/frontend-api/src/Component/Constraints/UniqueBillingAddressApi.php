<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Attribute;
use Override;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class UniqueBillingAddressApi extends Constraint
{
    public const DUPLICATE_BILLING_ADDRESS = '9732bc5c-7b8e-404b-ac8c-a1c810f6a045';

    public string $message = 'Billing address company number {{ company_number }} already exists. Contact customer support.';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::DUPLICATE_BILLING_ADDRESS => 'DUPLICATE_BILLING_ADDRESS',
    ];

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
