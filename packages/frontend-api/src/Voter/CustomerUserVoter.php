<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Voter;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Doctrine\Exception\UnexpectedTypeException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

class CustomerUserVoter extends AbstractB2bVoter
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Symfony\Component\Security\Core\Security $security
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     */
    public function __construct(
        Domain $domain,
        protected readonly Security $security,
        protected readonly CustomerUserFacade $customerUserFacade,
    ) {
        parent::__construct($domain);
    }

    /**
     * @param string $attribute
     * @param array $subject
     * @return bool
     */
    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === 'can_manage_customer_user_voter';
    }

    /**
     * @param string $attribute
     * @param \Overblog\GraphQLBundle\Definition\Argument|null $argument
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @return bool
     */
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

        if ($this->security->isGranted('ROLE_API_ALL')) {
            return $this->isRoleApiAllGranted($inputData, $token);
        }

        if ($this->security->isGranted('ROLE_API_CUSTOMER_SELF_MANAGE')) {
            return $this->isRoleApiCustomerSelfManageGranted($inputData, $token);
        }

        return false;
    }

    /**
     * @param array $inputData
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @return bool
     */
    protected function isUnauthenticatedAccessAllowed(array $inputData, TokenInterface $token): bool
    {
        return false;
    }

    /**
     * @param array $inputData
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @return bool
     */
    protected function isRoleApiAllGranted(array $inputData, TokenInterface $token): bool
    {
        /** @var \Shopsys\FrontendApiBundle\Model\User\FrontendApiUser $loggedUser */
        $loggedUser = $token->getUser();
        $loggedCustomerUser = $this->customerUserFacade->getByUuid($loggedUser->getUuid());
        $editedCustomerUser = $this->customerUserFacade->getByUuid($inputData['customerUserUuid']);

        return $loggedCustomerUser->getCustomer()->getId() === $editedCustomerUser->getCustomer()->getId();
    }

    /**
     * @param array $inputData
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @return bool
     */
    protected function isRoleApiCustomerSelfManageGranted(array $inputData, TokenInterface $token): bool
    {
        /** @var \Shopsys\FrontendApiBundle\Model\User\FrontendApiUser $loggedUser */
        $loggedUser = $token->getUser();
        $loggedUserUuid = $loggedUser->getUuid();
        $editedUserUuid = $inputData['customerUserUuid'];

        return $loggedUserUuid === $editedUserUuid;
    }
}
