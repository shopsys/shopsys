<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\OrderCancelledException;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\OrderNotFoundForWithdrawalException;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalAlreadyRequestedException;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalDeadlinePassedException;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalChecker;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class OrderWithdrawalRequestValidator extends ConstraintValidator
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalChecker $withdrawalChecker
     */
    public function __construct(
        protected readonly WithdrawalChecker $withdrawalChecker,
    ) {
    }

    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof OrderWithdrawalRequest) {
            throw new UnexpectedTypeException($constraint, OrderWithdrawalRequest::class);
        }

        try {
            $this->withdrawalChecker->checkOrderWithdrawal($value->orderUrlHash);
        } catch (OrderNotFoundForWithdrawalException) {
            $this->context->buildViolation($constraint->orderNotFoundMessage)
                ->setCode(OrderWithdrawalRequest::ORDER_NOT_FOUND_ERROR)
                ->atPath('orderUrlHash')
                ->addViolation();
        } catch (OrderCancelledException) {
            $this->context->buildViolation($constraint->orderCancelledMessage)
                ->setCode(OrderWithdrawalRequest::ORDER_CANCELLED_ERROR)
                ->atPath('orderUrlHash')
                ->addViolation();
        } catch (WithdrawalAlreadyRequestedException) {
            $this->context->buildViolation($constraint->alreadyRequestedMessage)
                ->setCode(OrderWithdrawalRequest::ALREADY_REQUESTED_ERROR)
                ->atPath('orderUrlHash')
                ->addViolation();
        } catch (WithdrawalDeadlinePassedException) {
            $this->context->buildViolation($constraint->withdrawalDeadlinePassedMessage)
                ->setCode(OrderWithdrawalRequest::WITHDRAWAL_DEADLINE_PASSED_ERROR)
                ->atPath('orderUrlHash')
                ->addViolation();
        }
    }
}
