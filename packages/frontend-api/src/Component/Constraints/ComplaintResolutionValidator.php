<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintResolutionEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ComplaintResolutionValidator extends ConstraintValidator
{
    public function __construct(
        protected readonly ComplaintResolutionEnum $complaintResolutionEnum,
    ) {
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Component\Constraints\ComplaintResolution $constraint
     */
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ComplaintResolution) {
            throw new UnexpectedTypeException($constraint, ComplaintResolution::class);
        }

        $resolution = $value->resolution;

        if (!in_array($resolution, $this->complaintResolutionEnum->getAllCases(), true)) {
            $this->context->buildViolation($constraint->selectedComplaintResolutionNotSupportedMessage)
                ->setCode($constraint::SELECTED_COMPLAINT_RESOLUTION_NOT_SUPPORTED_ERROR)
                ->atPath('resolution')
                ->addViolation();
        }

        if ($this->complaintResolutionEnum->isMoneyReturn($resolution) && ($value->bankAccountNumber === null || $value->bankAccountNumber === '')) {
            $this->context->buildViolation($constraint->selectedComplaintResolutionRequiresBankAccountFilledMessage)
                ->setCode($constraint::SELECTED_COMPLAINT_RESOLUTION_REQUIRES_BANK_ACCOUNT_NUMBER_FILLED_ERROR)
                ->atPath('bankAccountNumber')
                ->addViolation();
        }
    }
}
