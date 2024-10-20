<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Customer\User;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginInfoFactory;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeFacade;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\Exception\MissingCustomerUserLoginTypeException;

class CustomerUserResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeFacade $customerUserLoginTypeFacade
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\LoginInfoFactory $loginInfoFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver $customerUserRoleResolver
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     */
    public function __construct(
        protected readonly CustomerUserLoginTypeFacade $customerUserLoginTypeFacade,
        protected readonly LoginInfoFactory $loginInfoFactory,
        protected readonly CustomerUserRoleResolver $customerUserRoleResolver,
        protected readonly NewsletterFacade $newsletterFacade,
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
                $mostRecentLoginType = $this->customerUserLoginTypeFacade->findMostRecentLoginType($customerUser);

                if ($mostRecentLoginType === null) {
                    throw new MissingCustomerUserLoginTypeException();
                }

                return $this->loginInfoFactory->createFromCustomerUserLoginType($mostRecentLoginType);
            },
            'roles' => function (CustomerUser $customerUser) {
                return $this->customerUserRoleResolver->getRolesForCustomerUser($customerUser);
            },
            'newsletterSubscription' => function (CustomerUser $customerUser) {
                return $this->newsletterFacade->isSubscribed($customerUser);
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
