<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressFacade;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade;
use Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade as BaseCustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;

/**
 * @property \App\Model\Customer\User\CustomerUserRepository $customerUserRepository
 */
class CustomerUserFacade extends BaseCustomerUserFacade
{
    /**
     * @var \App\Model\Newsletter\NewsletterFacade
     */
    private $newsletterFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Customer\User\CustomerUserRepository $customerUserRepository
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade $customerMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressFactoryInterface $billingAddressFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface $billingAddressDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFactoryInterface $customerUserFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade $customerFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactoryInterface $customerDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressFacade $billingAddressFacade
     * @param \App\Model\Newsletter\NewsletterFacade $newsletterFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        CustomerUserRepository $customerUserRepository,
        CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory,
        CustomerMailFacade $customerMailFacade,
        BillingAddressFactoryInterface $billingAddressFactory,
        BillingAddressDataFactoryInterface $billingAddressDataFactory,
        CustomerUserFactoryInterface $customerUserFactory,
        CustomerUserPasswordFacade $customerUserPasswordFacade,
        CustomerFacade $customerFacade,
        DeliveryAddressFacade $deliveryAddressFacade,
        CustomerDataFactoryInterface $customerDataFactory,
        BillingAddressFacade $billingAddressFacade,
        NewsletterFacade $newsletterFacade,
        CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
    ) {
        parent::__construct(
            $em,
            $customerUserRepository,
            $customerUserUpdateDataFactory,
            $customerMailFacade,
            $billingAddressFactory,
            $billingAddressDataFactory,
            $customerUserFactory,
            $customerUserPasswordFacade,
            $customerFacade,
            $deliveryAddressFacade,
            $customerDataFactory,
            $billingAddressFacade,
            $customerUserRefreshTokenChainFacade
        );
        $this->newsletterFacade = $newsletterFacade;
    }

    /**
     * @param int $customerUserId
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData $customerUserUpdateData
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     * @return \App\Model\Customer\User\CustomerUser|\App\Model\Customer\User\CustomerUser
     */
    public function edit($customerUserId, CustomerUserUpdateData $customerUserUpdateData, ?DeliveryAddress $deliveryAddress = null)
    {

        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = parent::edit($customerUserId, $customerUserUpdateData);

        $newsletterSubscriber = $this->newsletterFacade->findNewsletterSubscriberByEmailAndDomainId($customerUser->getEmail(), $customerUser->getDomainId());

        if ($newsletterSubscriber === null && $customerUser->isNewsletterSubscription()
            || ($newsletterSubscriber !== null && $newsletterSubscriber->isDeleted() === true)
        ) {
            $this->newsletterFacade->addSubscribedEmail($customerUser->getEmail(), $customerUser->getDomainId());
        }

        if ($newsletterSubscriber !== null && !$customerUser->isNewsletterSubscription()) {
            $this->newsletterFacade->deleteById($newsletterSubscriber->getId());
        }

        return $customerUser;
    }

    /**
     * @param int $erpCustomerNumber
     * @return \App\Model\Customer\User\CustomerUser|null
     */
    public function findByErpCustomerNumber(int $erpCustomerNumber): ?CustomerUser
    {
        return $this->customerUserRepository->findByErpCustomerNumber($erpCustomerNumber);
    }
}
