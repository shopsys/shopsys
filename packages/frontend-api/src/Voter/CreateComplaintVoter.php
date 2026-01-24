<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Voter;

use Overblog\GraphQLBundle\Definition\Argument;
use Override;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CreateComplaintVoter extends Voter
{
    public function __construct(
        protected readonly Security $security,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly OrderApiFacade $orderApiFacade,
    ) {
    }

    #[Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === 'create_complaint_voter';
    }

    #[Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if ($this->security->isGranted(CustomerUserRole::ROLE_API_COMPLAINT_CREATION) === false) {
            return false;
        }

        if ($subject !== null && !$subject instanceof Argument) {
            throw new UnexpectedTypeException($subject, Argument::class);
        }

        $input = $subject['input'];
        $currentCustomerUser = $this->currentCustomerUser->getCurrentCustomerUser();
        $orderUuid = $input['orderUuid'];

        if ($orderUuid === null) {
            return true;
        }

        $order = $this->orderApiFacade->getByUuid($orderUuid);

        $isAllowedToCreateOrder = $this->security->isGranted(CustomerUserRole::ROLE_API_CART_AND_ORDER_CREATION);
        $isAllowedToAccessAllCompanyOrders = $this->security->isGranted(CustomerUserRole::ROLE_API_COMPANY_ORDERS_VIEW);

        $orderWasCreatedByCurrentCustomerUser = $currentCustomerUser === $order->getCustomerUser();
        $orderBelongsToCompanyOfCurrentCustomerUser = $currentCustomerUser->getCustomer() === $order->getCustomer();

        if ($orderWasCreatedByCurrentCustomerUser) {
            return $isAllowedToCreateOrder || $isAllowedToAccessAllCompanyOrders;
        }

        return $orderBelongsToCompanyOfCurrentCustomerUser && $isAllowedToAccessAllCompanyOrders;
    }
}
