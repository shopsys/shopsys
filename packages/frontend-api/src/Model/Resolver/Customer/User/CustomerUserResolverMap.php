<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Customer\User;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class CustomerUserResolverMap extends ResolverMap
{
    /**
     * @return array
     */
    protected function map(): array
    {
        $commonCustomerResolverFields = [
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
