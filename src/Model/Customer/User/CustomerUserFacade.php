<?php

declare(strict_types=1);


namespace App\Model\Customer\User;


use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade as BaseCustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;

class CustomerUserFacade extends BaseCustomerUserFacade
{
    /**
     * @var NewsletterFacade
     */
    private $newsletterFacade;

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
    ){
        parent::__construct($em, $customerUserRepository, $customerUserUpdateDataFactory, $customerMailFacade, $billingAddressFactory, $deliveryAddressFactory, $billingAddressDataFactory, $customerUserFactory, $customerUserPasswordFacade, $customerFacade);
        $this->newsletterFacade = $newsletterFacade;
    }


    /**
     * @param \App\Model\Customer\User\CustomerUserData $customerUserData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     * @throws \Shopsys\FrameworkBundle\Model\Customer\Exception\DuplicateEmailException
     */
    public function registerWithBillingAddress(CustomerUserData $customerUserData)
    {

        $billingAddress = $this->billingAddressDataFactory->create();
        $billingAddress->city = $customerUserData->city;
        $billingAddress->street = $customerUserData->street;
        $billingAddress->postcode = $customerUserData->postcode;

        $customer = $this->customerFacade->createCustomerWithBillingAddress($billingAddress);
        $customerUserData->customer = $customer;
        $deliveryAddress = null;
        /**
         * @var CustomerUser $customerUser
         */
        $customerUser = $this->customerUserFactory->create($customerUserData, $deliveryAddress);
        $this->setEmail($customerUserData->email, $customerUser);

        $this->em->persist($customerUser);
        $this->em->flush();

        if($customerUser->isAdvertisingApproval()){
            $this->newsletterFacade->addSubscribedEmail($customerUser->getEmail(), $customerUser->getDomainId());
        }

        $this->customerMailFacade->sendRegistrationMail($customerUser);
        return $customerUser;
    }

}