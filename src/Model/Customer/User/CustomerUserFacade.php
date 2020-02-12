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
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;

/**
 * @property \App\Model\Customer\User\CustomerUserRepository $customerUserRepository
 */
class CustomerUserFacade extends BaseCustomerUserFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade
     */
    private $newsletterFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Customer\User\CustomerUserRepository $customerUserRepository
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
     * @param int $customerUserId
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData $customerUserUpdateData
     * @return \App\Model\Customer\User\CustomerUser
     */
    public function edit($customerUserId, CustomerUserUpdateData $customerUserUpdateData)
    {

        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = parent::edit($customerUserId, $customerUserUpdateData);

        $newsletterSubscriber = $this->newsletterFacade->findNewsletterSubscriberByEmailAndDomainId($customerUser->getEmail(), $customerUser->getDomainId());
        if ($newsletterSubscriber == null && $customerUser->isNewsletterSubscription()) {
            $this->newsletterFacade->addSubscribedEmail($customerUser->getEmail(), $customerUser->getDomainId());
        }

        if ($newsletterSubscriber != null && !$customerUser->isNewsletterSubscription()) {
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
