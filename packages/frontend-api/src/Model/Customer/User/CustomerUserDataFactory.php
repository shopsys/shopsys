<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactory as BaseCustomerUserDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupRepository;

class CustomerUserDataFactory
{
    public function __construct(
        protected readonly BaseCustomerUserDataFactory $customerUserDataFactory,
        protected readonly Domain $domain,
        protected readonly CustomerUserRoleGroupRepository $customerUserRoleGroupRepository,
    ) {
    }

    public function createNewForCustomerWithArgument(Customer $customer, Argument $argument): CustomerUserData
    {
        $input = $argument['input'];

        $customerUserData = $this->customerUserDataFactory->createForCustomerWithPresetPricingGroup($customer);
        $customerUserData->sendRegistrationMail = true;

        return $this->mapInputDataToCustomerUserData($input, $customerUserData);
    }

    public function createForCustomerUserWithArgument(CustomerUser $customerUser, Argument $argument): CustomerUserData
    {
        $input = $argument['input'];

        $customerUserData = $this->customerUserDataFactory->createFromCustomerUser($customerUser);

        return $this->mapInputDataToCustomerUserData($input, $customerUserData);
    }

    protected function mapInputDataToCustomerUserData(
        array $input,
        CustomerUserData $customerUserData,
    ): CustomerUserData {
        foreach ($input as $key => $value) {
            if (property_exists(get_class($customerUserData), $key)) {
                $customerUserData->{$key} = $value ?? null;
            }
        }

        $this->setRoleGroup($customerUserData, $input['roleGroupUuid']);

        return $customerUserData;
    }

    protected function setRoleGroup(CustomerUserData $customerUserData, string $roleGroupUuid): void
    {
        $customerUserData->roleGroup = $this->customerUserRoleGroupRepository->getByUuid($roleGroupUuid);
    }
}
