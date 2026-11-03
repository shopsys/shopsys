<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Customer;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressRepository;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrontendApiBundle\Model\Customer\User\CustomerRegistrationRateLimiter;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class CustomerRegisteredQuery extends AbstractQuery
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly BillingAddressRepository $billingAddressRepository,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly CustomerRegistrationRateLimiter $customerRegistrationRateLimiter,
    ) {
    }

    public function isCustomerUserRegisteredQuery(string $email): bool
    {
        $this->customerRegistrationRateLimiter->consume();

        return $this->isCustomerUserRegistered($email);
    }

    public function couldBeCustomerRegisteredQuery(string $email, ?string $companyNumber): bool
    {
        $this->customerRegistrationRateLimiter->consume();

        $isCustomerUserRegistered = $this->isCustomerUserRegistered($email);

        if (!$this->domain->isB2b() || $companyNumber === null) {
            return !$isCustomerUserRegistered;
        }

        $billingAddressExists = $this->billingAddressRepository->findByCompanyNumberAndDomainId($companyNumber, $this->domain->getId()) !== null;

        return !$isCustomerUserRegistered && !$billingAddressExists;
    }

    protected function isCustomerUserRegistered(string $email): bool
    {
        $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain($email, $this->domain->getId());

        return $customerUser !== null;
    }
}
