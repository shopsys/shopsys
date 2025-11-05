<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Voter;

use Overblog\GraphQLBundle\Definition\Argument;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CompanyOwnerVoter extends AbstractB2bVoter
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Symfony\Bundle\SecurityBundle\Security $security
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
     * @param mixed $subject
     * @return bool
     */
    #[Override]
    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === 'is_company_owner_voter';
    }

    /**
     * @param string $attribute
     * @param \Overblog\GraphQLBundle\Definition\Argument|null $argument
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @return bool
     */
    #[Override]
    protected function checkAccess(string $attribute, ?Argument $argument, TokenInterface $token)
    {
        if ($this->security->isGranted('ROLE_API_MANAGE_CUSTOMERS')) {
            return $this->isRoleApiAllGranted($token);
        }

        return false;
    }

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @return bool
     */
    protected function isRoleApiAllGranted(TokenInterface $token): bool
    {
        /** @var \Shopsys\FrontendApiBundle\Model\User\FrontendApiUser $loggedUser */
        $loggedUser = $token->getUser();

        try {
            $loggedCustomerUser = $this->customerUserFacade->getByUuid($loggedUser->getUuid());

            return $loggedCustomerUser->getCustomer()->getBillingAddress()->isCompanyCustomer();
        } catch (CustomerUserNotFoundException $exception) {
            return false;
        }
    }
}
