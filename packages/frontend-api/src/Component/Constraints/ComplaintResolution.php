<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

class ComplaintResolution extends Constraint
{
    public const string SELECTED_COMPLAINT_RESOLUTION_NOT_SUPPORTED_ERROR = 'a05b7eae-364b-4a77-a2db-b37398c417b1';
    public const string SELECTED_COMPLAINT_RESOLUTION_REQUIRES_BANK_ACCOUNT_NUMBER_FILLED_ERROR = 'aea614ec-f673-432d-8eec-ac3f43b4ea60';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::SELECTED_COMPLAINT_RESOLUTION_NOT_SUPPORTED_ERROR => 'SELECTED_COMPLAINT_RESOLUTION_NOT_SUPPORTED_ERROR',
        self::SELECTED_COMPLAINT_RESOLUTION_REQUIRES_BANK_ACCOUNT_NUMBER_FILLED_ERROR => 'SELECTED_COMPLAINT_RESOLUTION_REQUIRES_BANK_ACCOUNT_FILLED_ERROR',
    ];

    /**
     * @param array<string, mixed>|null $options
     * @param string $selectedComplaintResolutionNotSupportedMessage
     * @param string $selecteComplaintResolutionRequiresBankAccountFilledMessage
     * @param array<string>|null $groups
     * @param mixed $payload
     */
    #[HasNamedArguments]
    public function __construct(
        ?array $options = null,
        public string $selectedComplaintResolutionNotSupportedMessage = 'Selected complaint resolution is not supported',
        public string $selecteComplaintResolutionRequiresBankAccountFilledMessage = 'Selected complaint resolution requires bank account number to be filled',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        if (is_array($options)) {
            DeprecationHelper::trigger(
                'Passing an array of options to configure the "%s" constraint is deprecated, use named arguments instead.',
                static::class,
            );
        }

        parent::__construct($options, $groups, $payload);
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
