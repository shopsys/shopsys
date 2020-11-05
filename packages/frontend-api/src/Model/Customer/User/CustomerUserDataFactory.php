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
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface
     */
    protected $customerUserDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface $customerUserDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        CustomerUserDataFactoryInterface $customerUserDataFactory,
        Domain $domain
    ) {
        $this->customerUserDataFactory = $customerUserDataFactory;
        $this->domain = $domain;
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
                $customerUserData->{$key} = $value !== null ? trim($value) : null;
            }
        }

        return $customerUserData;
    }
}
