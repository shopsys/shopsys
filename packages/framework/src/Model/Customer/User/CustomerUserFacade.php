<?php

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;

class CustomerUserFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository
     */
    protected $customerUserRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface
     */
    protected $customerUserUpdateDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade
     */
    protected $customerMailFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\BillingAddressFactoryInterface
     */
    protected $billingAddressFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactoryInterface
     */
    protected $deliveryAddressFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface
     */
    protected $billingAddressDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFactoryInterface
     */
    protected $customerUserFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade
     */
    protected $customerUserPasswordFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade
     */
    protected $customerFacade;

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
        CustomerFacade $customerFacade
    ) {
        $this->em = $em;
        $this->customerUserRepository = $customerUserRepository;
        $this->customerUserUpdateDataFactory = $customerUserUpdateDataFactory;
        $this->customerMailFacade = $customerMailFacade;
        $this->billingAddressFactory = $billingAddressFactory;
        $this->deliveryAddressFactory = $deliveryAddressFactory;
        $this->billingAddressDataFactory = $billingAddressDataFactory;
        $this->customerUserFactory = $customerUserFactory;
        $this->customerUserPasswordFacade = $customerUserPasswordFacade;
        $this->customerFacade = $customerFacade;
    }

    /**
     * @param int $customerUserId
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function getCustomerUserById($customerUserId)
    {
        return $this->customerUserRepository->getCustomerUserById($customerUserId);
    }

    /**
     * @param string $email
     * @param int $domainId
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    public function findCustomerUserByEmailAndDomain($email, $domainId)
    {
        return $this->customerUserRepository->findCustomerUserByEmailAndDomain($email, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function register(CustomerUserData $customerUserData)
    {
        $customer = $this->customerFacade->createCustomerWithBillingAddress($this->billingAddressDataFactory->create());
        $customerUser = $this->createCustomerUser($customer, $customerUserData);

        $this->customerMailFacade->sendRegistrationMail($customerUser);

        return $customerUser;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData $customerUserUpdateData
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function create(CustomerUserUpdateData $customerUserUpdateData)
    {
        $customer = $this->customerFacade->createCustomerWithBillingAddress($customerUserUpdateData->billingAddressData);
        $customerUser = $this->createCustomerUser($customer, $customerUserUpdateData->customerUserData, $customerUserUpdateData->deliveryAddressData);

        if ($customerUserUpdateData->sendRegistrationMail) {
            $this->customerMailFacade->sendRegistrationMail($customerUser);
        }

        return $customerUser;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData|null $deliveryAddressData
     *
     *@throws \Shopsys\FrameworkBundle\Model\Customer\Exception\DuplicateEmailException
     *@return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    protected function createCustomerUser(
        Customer $customer,
        CustomerUserData $customerUserData,
        ?DeliveryAddressData $deliveryAddressData = null
    ): CustomerUser {
        if ($deliveryAddressData) {
            $deliveryAddress = $this->deliveryAddressFactory->create($deliveryAddressData);
        } else {
            $deliveryAddress = null;
        }
        $customerUserData->customer = $customer;
        $customerUser = $this->customerUserFactory->create($customerUserData, $deliveryAddress);
        $this->setEmail($customerUserData->email, $customerUser);

        $this->em->persist($customerUser);
        $this->em->flush();

        return $customerUser;
    }

    /**
     * @param int $customerUserId
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData $customerUserUpdateData
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    protected function edit($customerUserId, CustomerUserUpdateData $customerUserUpdateData)
    {
        $customerUser = $this->getCustomerUserById($customerUserId);

        $customerUser->edit($customerUserUpdateData->customerUserData);

        if ($customerUserUpdateData->customerUserData->password !== null) {
            $this->customerUserPasswordFacade->changePassword($customerUser, $customerUserUpdateData->customerUserData->password);
        }

        $customerUser->getCustomer()->getBillingAddress()->edit($customerUserUpdateData->billingAddressData);

        $this->editDeliveryAddress($customerUser, $customerUserUpdateData->deliveryAddressData);

        return $customerUser;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData $deliveryAddressData
     */
    protected function editDeliveryAddress(CustomerUser $customerUser, DeliveryAddressData $deliveryAddressData): void
    {
        if (!$deliveryAddressData->addressFilled) {
            $customerUser->setDeliveryAddress(null);
            return;
        }

        $deliveryAddress = $customerUser->getDeliveryAddress();
        if ($deliveryAddress instanceof DeliveryAddress) {
            $deliveryAddress->edit($deliveryAddressData);
        } else {
            $customerUser->setDeliveryAddress($this->deliveryAddressFactory->create($deliveryAddressData));
        }
    }

    /**
     * @param int $customerUserId
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData $customerUserUpdateData
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function editByAdmin($customerUserId, CustomerUserUpdateData $customerUserUpdateData)
    {
        $customerUser = $this->edit($customerUserId, $customerUserUpdateData);

        $this->setEmail($customerUserUpdateData->customerUserData->email, $customerUser);

        $this->em->flush();

        return $customerUser;
    }

    /**
     * @param int $customerUserId
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData $customerUserUpdateData
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function editByCustomer($customerUserId, CustomerUserUpdateData $customerUserUpdateData)
    {
        $customerUser = $this->edit($customerUserId, $customerUserUpdateData);

        $this->em->flush();

        return $customerUser;
    }

    /**
     * @param int $customerUserId
     */
    public function delete($customerUserId)
    {
        $customerUser = $this->getCustomerUserById($customerUserId);

        $this->em->remove($customerUser);
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    public function amendUserDataFromOrder(CustomerUser $customerUser, Order $order)
    {
        $this->edit(
            $customerUser->getId(),
            $this->customerUserUpdateDataFactory->createAmendedByOrder($customerUser, $order)
        );

        $this->em->flush();
    }

    /**
     * @param string $email
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     */
    protected function setEmail(string $email, CustomerUser $customerUser): void
    {
        $customerUserByEmailAndDomain = $this->findCustomerUserByEmailAndDomain(
            $email,
            $customerUser->getDomainId()
        );

        if ($customerUserByEmailAndDomain !== null && $customerUser->getId() !== $customerUserByEmailAndDomain->getId()) {
            throw new \Shopsys\FrameworkBundle\Model\Customer\Exception\DuplicateEmailException($email);
        }

        $customerUser->setEmail($email);
    }
}
