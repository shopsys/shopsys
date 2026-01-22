<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;

class UniqueBillingAddress extends Constraint
{
    public const string DUPLICATE_BILLING_ADDRESS = 'dc6b5879-cb7a-423b-bd97-d9c667d96fd5';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::DUPLICATE_BILLING_ADDRESS => 'DUPLICATE_BILLING_ADDRESS',
    ];

    /**
     * @param string $errorPath
     * @param string $message
     * @param array|null $groups
     * @param mixed $payload
     */
    public function __construct(
        public string $errorPath,
        public string $message = 'Billing address company number {{ company_number }} already exists for domain {{ domain_id }}.',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct([], $groups, $payload);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
