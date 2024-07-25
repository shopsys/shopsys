<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface;

class CustomerUserDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface $customerUserDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly CustomerUserDataFactoryInterface $customerUserDataFactory,
        protected readonly Domain $domain,
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

        return $customerUserData;
    }
}
