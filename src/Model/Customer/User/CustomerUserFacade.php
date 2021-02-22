<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use App\Component\String\HashGenerator;
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
 * @property \App\Model\Customer\Mail\CustomerMailFacade $customerMailFacade
 */
class CustomerUserFacade extends BaseCustomerUserFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade
     */
    private $newsletterFacade;

    /**
     * @var \App\Component\String\HashGenerator
     */
    private HashGenerator $hashGenerator;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository $customerUserRepository
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory
     * @param \App\Model\Customer\Mail\CustomerMailFacade $customerMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressFactoryInterface $billingAddressFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface $billingAddressDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFactoryInterface $customerUserFactory
     * @param \App\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade $customerFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactoryInterface $customerDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressFacade $billingAddressFacade
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
     * @param \App\Component\String\HashGenerator $hashGenerator
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
        CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade,
        HashGenerator $hashGenerator
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
        $this->hashGenerator = $hashGenerator;
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

        if ($newsletterSubscriber === null && $customerUser->isNewsletterSubscription()) {
            $this->newsletterFacade->addSubscribedEmail($customerUser->getEmail(), $customerUser->getDomainId());
        }

        if ($newsletterSubscriber !== null && !$customerUser->isNewsletterSubscription()) {
            $this->newsletterFacade->deleteById($newsletterSubscriber->getId());
        }

        return $customerUser;
    }

    /**
     * @param string|null $email
     * @param int $domainId
     * @return bool[]
     */
    public function getCustomerInfo(?string $email, int $domainId): array
    {
        $customerInfo = [
            'exists' => false,
            'activated' => false,
            'isCompanyCustomer' => false,
        ];

        $customerUser = $this->findCustomerUserByEmailAndDomain($email, $domainId);
        if ($customerUser !== null) {
            /** @var \App\Model\Customer\BillingAddress $billingAddress */
            $billingAddress = $customerUser->getCustomer()->getBillingAddress();

            $customerInfo['exists'] = true;
            $customerInfo['activated'] = $billingAddress->isActivated();
            $customerInfo['isCompanyCustomer'] = $billingAddress->isCompanyCustomer();
        }

        return $customerInfo;
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData $customerUserUpdateData
     */
    public function activeCustomerUser(CustomerUser $customerUser, CustomerUserUpdateData $customerUserUpdateData): void
    {
        /** @var \App\Model\Customer\BillingAddress $billingAddress */
        $billingAddress = $customerUser->getCustomer()->getBillingAddress();

        if ($billingAddress->isActivated()) {
            return;
        }

        $this->edit($customerUser->getId(), $customerUserUpdateData);
        $billingAddress->activate();
        $this->em->flush();

        $this->customerMailFacade->sendRegistrationMail($customerUser);
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     */
    public function sendActivationMail(CustomerUser $customerUser): void
    {
        $resetPasswordHash = $this->hashGenerator->generateHash(CustomerUserPasswordFacade::RESET_PASSWORD_HASH_LENGTH);
        $customerUser->setResetPasswordHash($resetPasswordHash);
        $this->em->flush();

        $this->customerMailFacade->sendActivationMail($customerUser);
    }
}
