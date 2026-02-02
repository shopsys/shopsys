<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;

class ComplaintResolution extends Constraint
{
    public const string SELECTED_COMPLAINT_RESOLUTION_NOT_SUPPORTED_ERROR = 'a05b7eae-364b-4a77-a2db-b37398c417b1';
    public const string SELECTED_COMPLAINT_RESOLUTION_REQUIRES_BANK_ACCOUNT_NUMBER_FILLED_ERROR = 'aea614ec-f673-432d-8eec-ac3f43b4ea60';

    public string $selectedComplaintResolutionNotSupportedMessage = 'Selected complaint resolution is not supported';

    public string $selecteComplaintResolutionRequiresBankAccountFilledMessage = 'Selected complaint resolution requires bank account number to be filled';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::SELECTED_COMPLAINT_RESOLUTION_NOT_SUPPORTED_ERROR => 'SELECTED_COMPLAINT_RESOLUTION_NOT_SUPPORTED_ERROR',
        self::SELECTED_COMPLAINT_RESOLUTION_REQUIRES_BANK_ACCOUNT_NUMBER_FILLED_ERROR => 'SELECTED_COMPLAINT_RESOLUTION_REQUIRES_BANK_ACCOUNT_FILLED_ERROR',
    ];

    #[Override]
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
