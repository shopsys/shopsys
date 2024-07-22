<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Customer\User;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginInfoFactory;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeFacade;

class CustomerUserResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeFacade $customerUserLoginTypeFacade
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\LoginInfoFactory $loginInfoFactory
     */
    public function __construct(
        protected readonly CustomerUserLoginTypeFacade $customerUserLoginTypeFacade,
        protected readonly LoginInfoFactory $loginInfoFactory,
    ) {
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        $commonCustomerResolverFields = [
            'billingAddressUuid' => function (CustomerUser $customerUser) {
                return $customerUser->getCustomer()->getBillingAddress()->getUuid();
            },
            'street' => function (CustomerUser $customerUser) {
                return $customerUser->getCustomer()->getBillingAddress()->getStreet();
            },
            'city' => function (CustomerUser $customerUser) {
                return $customerUser->getCustomer()->getBillingAddress()->getCity();
            },
            'postcode' => function (CustomerUser $customerUser) {
                return $customerUser->getCustomer()->getBillingAddress()->getPostcode();
            },
            'country' => function (CustomerUser $customerUser) {
                return $customerUser->getCustomer()->getBillingAddress()->getCountry();
            },
            'defaultDeliveryAddress' => function (CustomerUser $customerUser) {
                return $customerUser->getDefaultDeliveryAddress();
            },
            'deliveryAddresses' => function (CustomerUser $customerUser) {
                return $customerUser->getCustomer()->getDeliveryAddresses();
            },
            'pricingGroup' => function (CustomerUser $customerUser) {
                return $customerUser->getPricingGroup()->getName();
            },
            'loginInfo' => function (CustomerUser $customerUser) {
                $mostRecentLoginType = $this->customerUserLoginTypeFacade->getMostRecentLoginType($customerUser);

                return $this->loginInfoFactory->createFromCustomerUserLoginType($mostRecentLoginType);
            },
        ];

        return [
            'CustomerUser' => [
                self::RESOLVE_TYPE => function (CustomerUser $customerUser) {
                    if ($customerUser->getCustomer()->getBillingAddress()->isCompanyCustomer()) {
                        return 'CompanyCustomerUser';
                    }

                    return 'RegularCustomerUser';
                },
            ],
            'RegularCustomerUser' => $commonCustomerResolverFields,
            'CompanyCustomerUser' => $commonCustomerResolverFields + [
                'companyName' => function (CustomerUser $customerUser) {
                    return $customerUser->getCustomer()->getBillingAddress()->getCompanyName();
                },
                'companyNumber' => function (CustomerUser $customerUser) {
                    return $customerUser->getCustomer()->getBillingAddress()->getCompanyNumber();
                },
                'companyTaxNumber' => function (CustomerUser $customerUser) {
                    return $customerUser->getCustomer()->getBillingAddress()->getCompanyTaxNumber();
                },
            ],
        ];
    }
}
