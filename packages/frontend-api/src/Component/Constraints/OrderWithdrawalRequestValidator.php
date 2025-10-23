<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use DateTime;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class OrderWithdrawalRequestValidator extends ConstraintValidator
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly Domain $domain,
        protected readonly Setting $setting,
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

        $orderUrlHash = $value->orderUrlHash;

        try {
            $order = $this->orderFacade->getByUrlHashAndDomain($orderUrlHash, $this->domain->getId());
        } catch (OrderNotFoundException) {
            $this->context->buildViolation($constraint->orderNotFoundMessage)
                ->setCode(OrderWithdrawalRequest::ORDER_NOT_FOUND_ERROR)
                ->atPath('orderUrlHash')
                ->addViolation();

            return;
        }

        if ($order->isCancelled()) {
            $this->context->buildViolation($constraint->orderCancelledMessage)
                ->setCode(OrderWithdrawalRequest::ORDER_CANCELLED_ERROR)
                ->atPath('orderUrlHash')
                ->addViolation();

            return;
        }

        if ($order->getWithdrawalRequestedAt() !== null) {
            $this->context->buildViolation($constraint->alreadyRequestedMessage)
                ->setCode(OrderWithdrawalRequest::ALREADY_REQUESTED_ERROR)
                ->atPath('orderUrlHash')
                ->addViolation();

            return;
        }

        $deliveredAt = $order->getDeliveredAt();

        if ($deliveredAt === null) {
            return;
        }

        $withdrawalDeadlineDays = $this->setting->getForDomain(
            Setting::WITHDRAWAL_DEADLINE_DAYS,
            $order->getDomainId(),
        );

        $withdrawalDeadline = (clone $deliveredAt)->modify(sprintf('+%d days', $withdrawalDeadlineDays));
        $now = new DateTime();

        if ($now > $withdrawalDeadline) {
            $this->context->buildViolation($constraint->withdrawalDeadlinePassedMessage)
                ->setCode(OrderWithdrawalRequest::WITHDRAWAL_DEADLINE_PASSED_ERROR)
                ->atPath('orderUrlHash')
                ->addViolation();
        }
    }
}
