<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupRepository;

class CustomerUserDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface $customerUserDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupRepository $customerUserRoleGroupRepository
     */
    public function __construct(
        protected readonly CustomerUserDataFactoryInterface $customerUserDataFactory,
        protected readonly Domain $domain,
        protected readonly CustomerUserRoleGroupRepository $customerUserRoleGroupRepository,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    public function createWithArgument(Argument $argument): CustomerUserData
    {
        $input = $argument['input'];

        $domainId = $this->domain->getId();
        $customerUserData = $this->customerUserDataFactory->createForDomainId($domainId);

        return $this->mapInputDataToCustomerUserData($input, $customerUserData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    public function createNewForCustomerWithArgument(Customer $customer, Argument $argument): CustomerUserData
    {
        $input = $argument['input'];

        $customerUserData = $this->customerUserDataFactory->createForCustomerWithPresetPricingGroup($customer);
        $customerUserData->sendRegistrationMail = true;

        return $this->mapInputDataToCustomerUserData($input, $customerUserData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    public function createForCustomerUserWithArgument(CustomerUser $customerUser, Argument $argument): CustomerUserData
    {
        $input = $argument['input'];

        $customerUserData = $this->customerUserDataFactory->createFromCustomerUser($customerUser);

        return $this->mapInputDataToCustomerUserData($input, $customerUserData);
    }

    /**
     * @param array $input
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     * @param string $roleGroupUuid
     */
    protected function setRoleGroup(CustomerUserData $customerUserData, string $roleGroupUuid): void
    {
        $customerUserData->roleGroup = $this->customerUserRoleGroupRepository->getByUuid($roleGroupUuid);
    }
}
