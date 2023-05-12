<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
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
        protected readonly Domain $domain
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

        foreach ($input as $key => $value) {
            if (property_exists(get_class($customerUserData), $key)) {
                $customerUserData->{$key} = $value !== null ? $value : null;
            }
        }

        return $customerUserData;
    }
}
