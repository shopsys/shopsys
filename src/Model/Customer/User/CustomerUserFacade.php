<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade as BaseCustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;

class CustomerUserFacade extends BaseCustomerUserFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade
     */
    private $newsletterFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository $customerUserRepository
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade $customerMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressFactoryInterface $billingAddressFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactoryInterface $deliveryAddressFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface $billingAddressDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFactoryInterface $customerUserFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade $customerFacade
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        CustomerUserRepository $customerUserRepository,
        CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory,
        CustomerMailFacade $customerMailFacade,
        BillingAddressFactoryInterface $billingAddressFactory,
        DeliveryAddressFactoryInterface $deliveryAddressFactory,
        BillingAddressDataFactoryInterface $billingAddressDataFactory,
        CustomerUserFactoryInterface $customerUserFactory,
        CustomerUserPasswordFacade $customerUserPasswordFacade,
        CustomerFacade $customerFacade,
        NewsletterFacade $newsletterFacade
    ) {
        parent::__construct($em, $customerUserRepository, $customerUserUpdateDataFactory, $customerMailFacade, $billingAddressFactory, $deliveryAddressFactory, $billingAddressDataFactory, $customerUserFactory, $customerUserPasswordFacade, $customerFacade);
        $this->newsletterFacade = $newsletterFacade;
    }

    /**
     * @param \App\Model\Customer\User\CustomerUserData $customerUserData
     * @throws \Shopsys\FrameworkBundle\Model\Customer\Exception\DuplicateEmailException
     * @return \App\Model\Customer\User\CustomerUser
     */
    public function register(CustomerUserData $customerUserData): CustomerUser
    {
        $billingAddress = $this->billingAddressDataFactory->create();
        $billingAddress->city = $customerUserData->city;
        $billingAddress->street = $customerUserData->street;
        $billingAddress->postcode = $customerUserData->postcode;

        $customer = $this->customerFacade->createCustomerWithBillingAddress($billingAddress);
        $customerUserData->customer = $customer;
        $deliveryAddress = null;
        /**
         * @var \App\Model\Customer\User\CustomerUser
         */
        $customerUser = $this->customerUserFactory->create($customerUserData, $deliveryAddress);
        $this->setEmail($customerUserData->email, $customerUser);

        $this->em->persist($customerUser);
        $this->em->flush();

        if ($customerUser->isNewsletterSubscription()) {
            $this->newsletterFacade->addSubscribedEmail($customerUser->getEmail(), $customerUser->getDomainId());
        }

        $this->customerMailFacade->sendRegistrationMail($customerUser);
        return $customerUser;
    }
}
