<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Order;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\OrderCancelledException;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalAlreadyRequestedException;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalConfirmationHashInvalidException;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalDeadlinePassedException;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestDataFactory;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Order\WithdrawalRequestApiFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\OrderCancelledUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\OrderNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\TooManyWithdrawalRequestAttemptsUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\WithdrawalAlreadyRequestedUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\WithdrawalConfirmationInvalidUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\WithdrawalDeadlinePassedUserError;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

class OrderWithdrawalRequestMutation extends AbstractMutation
{
    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly WithdrawalRequestApiFacade $withdrawalRequestApiFacade,
        protected readonly Domain $domain,
        protected readonly WithdrawalRequestDataFactory $withdrawalRequestDataFactory,
        protected readonly RateLimiterFactoryInterface $orderWithdrawalRequestRateLimiter,
        protected readonly RequestStack $requestStack,
    ) {
    }

    public function orderWithdrawalRequestMutation(Argument $argument): bool
    {
        $input = $argument['input'];

        try {
            $order = $this->orderFacade->getByUrlHashAndDomain($input['orderUrlHash'], $this->domain->getId());
            $withdrawalRequestData = $this->withdrawalRequestDataFactory->createFromArray($input);

            if ($order->getCustomerUser() === null) {
                $this->checkOrderWithdrawalRequestRateLimit();
                $this->withdrawalRequestApiFacade->requestWithdrawalConfirmation($order, $withdrawalRequestData);
            } else {
                $withdrawalRequestData->confirmed = true;
                $this->withdrawalRequestApiFacade->createWithdrawalRequest($order, $withdrawalRequestData);
            }

            return true;
        } catch (OrderNotFoundException) {
            throw new OrderNotFoundUserError('Order not found');
        } catch (OrderCancelledException) {
            throw new OrderCancelledUserError('Withdrawal is not allowed for cancelled orders');
        } catch (WithdrawalAlreadyRequestedException) {
            throw new WithdrawalAlreadyRequestedUserError('Withdrawal has already been requested for this order');
        } catch (WithdrawalDeadlinePassedException) {
            throw new WithdrawalDeadlinePassedUserError('Withdrawal deadline has passed for this order');
        }
    }

    public function confirmOrderWithdrawalRequestMutation(Argument $argument): string
    {
        $confirmationHash = $argument['confirmationHash'];

        if ($confirmationHash === null || $confirmationHash === '') {
            throw new WithdrawalConfirmationInvalidUserError('Withdrawal confirmation hash is invalid or expired');
        }

        try {
            $order = $this->withdrawalRequestApiFacade->confirmWithdrawalRequest($confirmationHash);

            return $order->getUrlHash();
        } catch (WithdrawalConfirmationHashInvalidException) {
            throw new WithdrawalConfirmationInvalidUserError('Withdrawal confirmation hash is invalid or expired');
        } catch (OrderCancelledException) {
            throw new OrderCancelledUserError('Withdrawal is not allowed for cancelled orders');
        } catch (WithdrawalAlreadyRequestedException) {
            throw new WithdrawalAlreadyRequestedUserError('Withdrawal has already been requested for this order');
        } catch (WithdrawalDeadlinePassedException) {
            throw new WithdrawalDeadlinePassedUserError('Withdrawal deadline has passed for this order');
        }
    }

    protected function checkOrderWithdrawalRequestRateLimit(): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $clientIp = $request?->getClientIp() ?? 'unknown';

        $limit = $this->orderWithdrawalRequestRateLimiter
            ->create('order-withdrawal-request:' . $clientIp)
            ->consume();

        if (!$limit->isAccepted()) {
            throw new TooManyWithdrawalRequestAttemptsUserError('Too many withdrawal request attempts. Try again later.');
        }
    }
}
