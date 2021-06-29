<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Customer\User;

use App\Model\Country\CountryFacade;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface;
use Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserUpdateDataFactory as BaseCustomerUserUpdateDataFactory;

class CustomerUserUpdateDataFactory extends BaseCustomerUserUpdateDataFactory
{
    /**
     * @var \App\Model\Country\CountryFacade
     */
    protected CountryFacade $countryFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory
     * @param \App\Model\Country\CountryFacade $countryFacade
     */
    public function __construct(
        CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory,
        CountryFacade $countryFacade
    ) {
        parent::__construct($customerUserUpdateDataFactory);

        $this->countryFacade = $countryFacade;
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData
     */
    public function createFromCustomerUserWithArgument(CustomerUser $customerUser, Argument $argument): CustomerUserUpdateData
    {
        $input = $argument['input'];

        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromCustomerUser($customerUser);
        $customerUserData = $customerUserUpdateData->customerUserData;
        $billingAddressData = $customerUserUpdateData->billingAddressData;

        foreach ($input as $key => $value) {
            if (property_exists(get_class($customerUserData), $key)) {
                $customerUserData->{$key} = $value;
            }

            if (property_exists(get_class($billingAddressData), $key)) {
                $billingAddressData->{$key} = $value;
            }

            $billingAddressData->country = $this->countryFacade->findByCode($input['country']);
        }

        return $customerUserUpdateData;
    }
}
