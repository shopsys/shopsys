<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use App\Component\String\HashGenerator;
use App\Model\Administrator\Administrator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressFacade;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade;
use Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser as BaseCustomerUser;
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
 * @property \App\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
 * @method \App\Model\Customer\User\CustomerUser getByUuid(string $uuid)
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
     * @param \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
     * @param \App\Model\Customer\Mail\CustomerMailFacade $customerMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressFactoryInterface $billingAddressFactory
     * @param \App\Model\Customer\BillingAddressDataFactory $billingAddressDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFactoryInterface $customerUserFactory
     * @param \App\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade $customerFacade
     * @param \App\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactoryInterface $customerDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressFacade $billingAddressFacade
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     * @param \App\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
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
     * @param \App\Model\Customer\User\CustomerUserUpdateData $customerUserUpdateData
     * @param \App\Model\Customer\DeliveryAddress|null $deliveryAddress
     * @return \App\Model\Customer\User\CustomerUser
     */
    public function edit($customerUserId, CustomerUserUpdateData $customerUserUpdateData, ?DeliveryAddress $deliveryAddress = null)
    {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = parent::edit($customerUserId, $customerUserUpdateData, $deliveryAddress);

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
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param string $refreshTokenChain
     * @param string $deviceId
     * @param \DateTime $tokenExpiration
     * @param \App\Model\Administrator\Administrator|null $administrator
     */
    public function addRefreshTokenChain(
        BaseCustomerUser $customerUser,
        string $refreshTokenChain,
        string $deviceId,
        DateTime $tokenExpiration,
        ?Administrator $administrator = null
    ): void {
        $refreshTokenChain = $this->customerUserRefreshTokenChainFacade->createCustomerUserRefreshTokenChain(
            $customerUser,
            $refreshTokenChain,
            $deviceId,
            $tokenExpiration,
            $administrator
        );

        $customerUser->addRefreshTokenChain($refreshTokenChain);

        $this->em->flush();
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
