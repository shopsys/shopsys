<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Voter;

use Overblog\GraphQLBundle\Definition\Argument;
use Override;
use Shopsys\FrameworkBundle\Component\Doctrine\Exception\UnexpectedTypeException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CustomerUserVoter extends AbstractB2bVoter
{
    public function __construct(
        Domain $domain,
        protected readonly Security $security,
        protected readonly CustomerUserFacade $customerUserFacade,
    ) {
        parent::__construct($domain);
    }

    /**
     * @param array $subject
     */
    #[Override]
    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === 'can_manage_customer_user_voter';
    }

    #[Override]
    protected function checkAccess(string $attribute, ?Argument $argument, TokenInterface $token): bool
    {
        if ($argument === null) {
            throw new UnexpectedTypeException(
                sprintf('Argument is required for voter `%s`.', static::class),
            );
        }

        $inputData = $argument['input'];

        if ($token->getUser() === null) {
            return $this->isUnauthenticatedAccessAllowed($inputData, $token);
        }

        if ($this->security->isGranted(CustomerUserRole::ROLE_API_MANAGE_CUSTOMERS)) {
            return $this->isEditedCustomerUserFromSameCompany($inputData, $token);
        }

        return false;
    }

    protected function isUnauthenticatedAccessAllowed(array $inputData, TokenInterface $token): bool
    {
        return false;
    }

    protected function isEditedCustomerUserFromSameCompany(array $inputData, TokenInterface $token): bool
    {
        /** @var \Shopsys\FrontendApiBundle\Model\User\FrontendApiUser $loggedUser */
        $loggedUser = $token->getUser();

        try {
            $loggedCustomerUser = $this->customerUserFacade->getByUuid($loggedUser->getUuid());
            $editedCustomerUser = $this->customerUserFacade->getByUuid($inputData['customerUserUuid']);
        } catch (CustomerUserNotFoundException $exception) {
            return false;
        }

        return $loggedCustomerUser->getCustomer()->getId() === $editedCustomerUser->getCustomer()->getId();
    }
}
