<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Customer\User;

use App\Model\Customer\User\CustomerUser;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

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
                return $customerUser->getCustomer()->getBillingAddress()->getCountry()->getCode();
            },
        ];

        return [
            'CurrentCustomerUser' => [
                self::RESOLVE_TYPE => function (CustomerUser $customerUser) {
                    if ($customerUser->getCustomer()->getBillingAddress()->isCompanyCustomer()) {
                        return 'CurrentCompanyCustomerUser';
                    }

                    return 'CurrentRegularCustomerUser';
                },
            ],
            'CurrentRegularCustomerUser' => $commonCustomerResolverFields,
            'CurrentCompanyCustomerUser' => $commonCustomerResolverFields + [
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
