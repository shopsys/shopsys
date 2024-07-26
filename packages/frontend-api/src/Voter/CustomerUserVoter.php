<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class CustomerUserVoter extends Voter
{
    /**
     * @param \Symfony\Component\Security\Core\Security $security
     */
    public function __construct(
        protected readonly Security $security,
    ) {
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
     * @param array $subject
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $inputData = $subject['input'];

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
        return true;
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
