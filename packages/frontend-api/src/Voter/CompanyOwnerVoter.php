<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Voter;

use Overblog\GraphQLBundle\Definition\Argument;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CompanyOwnerVoter extends AbstractB2bVoter
{
    public function __construct(
        Domain $domain,
        protected readonly Security $security,
        protected readonly CustomerUserFacade $customerUserFacade,
    ) {
        parent::__construct($domain);
    }

    #[Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === 'is_company_owner_voter';
    }

    #[Override]
    protected function checkAccess(string $attribute, ?Argument $argument, TokenInterface $token): bool
    {
        if ($this->security->isGranted(CustomerUserRole::ROLE_API_MANAGE_CUSTOMERS)) {
            return $this->isCompanyCustomer($token);
        }

        return false;
    }

    protected function isCompanyCustomer(TokenInterface $token): bool
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
