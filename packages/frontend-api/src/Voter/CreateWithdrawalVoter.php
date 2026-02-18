<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Voter;

use Overblog\GraphQLBundle\Definition\Argument;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CreateWithdrawalVoter extends Voter
{
    public function __construct(
        protected readonly Security $security,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly OrderFacade $orderFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === 'create_withdrawal_voter';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
        ?Vote $vote = null,
    ): bool {
        if ($subject !== null && !$subject instanceof Argument) {
            throw new UnexpectedTypeException($subject, Argument::class);
        }

        $input = $subject['input'];
        $orderUrlHash = $input['orderUrlHash'];

        if ($orderUrlHash === null) {
            return true;
        }

        try {
            $order = $this->orderFacade->getByUrlHashAndDomain($orderUrlHash, $this->domain->getId());
        } catch (OrderNotFoundException) {
            return true;
        }

        $currentCustomerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if ($currentCustomerUser === null) {
            return $order->getCustomerUser() === null;
        }

        if ($this->security->isGranted(CustomerUserRole::ROLE_API_CART_AND_ORDER_CREATION) === false) {
            return false;
        }

        if ($order->getCustomerUser() === null) {
            return true;
        }

        $orderWasCreatedByCurrentCustomerUser = $currentCustomerUser->getId() === $order->getCustomerUser()->getId();

        if ($orderWasCreatedByCurrentCustomerUser) {
            return true;
        }

        $isAllowedToAccessAllCompanyOrders = $this->security->isGranted(CustomerUserRole::ROLE_API_COMPANY_ORDERS_VIEW);

        $orderCustomer = $order->getCustomer();
        $orderBelongsToCompanyOfCurrentCustomerUser = $orderCustomer !== null
            && $currentCustomerUser->getCustomer()->getId() === $orderCustomer->getId();

        return $orderBelongsToCompanyOfCurrentCustomerUser && $isAllowedToAccessAllCompanyOrders;
    }
}
