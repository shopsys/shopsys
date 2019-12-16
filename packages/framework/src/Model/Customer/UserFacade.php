<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;

class UserFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\UserRepository
     */
    protected $userRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerUserUpdateDataFactoryInterface
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
     * @var \Shopsys\FrameworkBundle\Model\Customer\UserFactoryInterface
     */
    protected $userFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\UserPasswordFacade
     */
    protected $userPasswordFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade
     */
    protected $customerFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Customer\UserRepository $userRepository
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade $customerMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressFactoryInterface $billingAddressFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactoryInterface $deliveryAddressFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface $billingAddressDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\UserFactoryInterface $userFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\UserPasswordFacade $userPasswordFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade $customerFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        UserRepository $userRepository,
        CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory,
        CustomerMailFacade $customerMailFacade,
        BillingAddressFactoryInterface $billingAddressFactory,
        DeliveryAddressFactoryInterface $deliveryAddressFactory,
        BillingAddressDataFactoryInterface $billingAddressDataFactory,
        UserFactoryInterface $userFactory,
        UserPasswordFacade $userPasswordFacade,
        CustomerFacade $customerFacade
    ) {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->customerUserUpdateDataFactory = $customerUserUpdateDataFactory;
        $this->customerMailFacade = $customerMailFacade;
        $this->billingAddressFactory = $billingAddressFactory;
        $this->deliveryAddressFactory = $deliveryAddressFactory;
        $this->billingAddressDataFactory = $billingAddressDataFactory;
        $this->userFactory = $userFactory;
        $this->userPasswordFacade = $userPasswordFacade;
        $this->customerFacade = $customerFacade;
    }

    /**
     * @param int $userId
     * @return \Shopsys\FrameworkBundle\Model\Customer\User
     */
    public function getUserById($userId)
    {
        return $this->userRepository->getUserById($userId);
    }

    /**
     * @param string $email
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Customer\User|null
     */
    public function findUserByEmailAndDomain($email, $domainId)
    {
        return $this->userRepository->findUserByEmailAndDomain($email, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\UserData $userData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User
     */
    public function register(UserData $userData)
    {
        $customer = $this->customerFacade->createCustomer();
        $userData->customer = $customer;

        $this->customerFacade->createCustomerWithBillingAddress(
            $customer,
            $this->billingAddressFactory->create($this->billingAddressDataFactory->createForCustomer($customer))
        );
        $user = $this->createUser($userData);

        $this->customerMailFacade->sendRegistrationMail($user);

        return $user;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUserUpdateData $customerUserUpdateData
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User
     */
    public function create(CustomerUserUpdateData $customerUserUpdateData)
    {
        $this->customerFacade->createCustomerWithBillingAddress(
            $customerUserUpdateData->billingAddressData->customer,
            $this->billingAddressFactory->create($customerUserUpdateData->billingAddressData)
        );
        $user = $this->createUser($customerUserUpdateData->userData, $customerUserUpdateData->deliveryAddressData);

        if ($customerUserUpdateData->sendRegistrationMail) {
            $this->customerMailFacade->sendRegistrationMail($user);
        }

        return $user;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\UserData $userData
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData|null $deliveryAddressData
     *
     * @throws \Shopsys\FrameworkBundle\Model\Customer\Exception\DuplicateEmailException
     * @return \Shopsys\FrameworkBundle\Model\Customer\User
     */
    protected function createUser(
        UserData $userData,
        ?DeliveryAddressData $deliveryAddressData = null
    ): User {
        if ($deliveryAddressData) {
            $deliveryAddress = $this->deliveryAddressFactory->create($deliveryAddressData);
        } else {
            $deliveryAddress = null;
        }

        $user = $this->userFactory->create($userData, $deliveryAddress);
        $this->setEmail($userData->email, $user);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    /**
     * @param int $userId
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUserUpdateData $customerUserUpdateData
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User
     */
    protected function edit($userId, CustomerUserUpdateData $customerUserUpdateData)
    {
        $user = $this->getUserById($userId);

        $user->edit($customerUserUpdateData->userData);

        if ($customerUserUpdateData->userData->password !== null) {
            $this->userPasswordFacade->changePassword($user, $customerUserUpdateData->userData->password);
        }

        $user->getCustomer()->getBillingAddress()->edit($customerUserUpdateData->billingAddressData);

        $this->editDeliveryAddress($user, $customerUserUpdateData->deliveryAddressData);

        return $user;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User $user
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData $deliveryAddressData
     */
    protected function editDeliveryAddress(User $user, DeliveryAddressData $deliveryAddressData): void
    {
        if (!$deliveryAddressData->addressFilled) {
            $user->setDeliveryAddress(null);
            return;
        }

        $deliveryAddress = $user->getDeliveryAddress();
        if ($deliveryAddress instanceof DeliveryAddress) {
            $deliveryAddress->edit($deliveryAddressData);
        } else {
            $user->setDeliveryAddress($this->deliveryAddressFactory->create($deliveryAddressData));
        }
    }

    /**
     * @param int $userId
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUserUpdateData $customerUserUpdateData
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User
     */
    public function editByAdmin($userId, CustomerUserUpdateData $customerUserUpdateData)
    {
        $user = $this->edit($userId, $customerUserUpdateData);

        $this->setEmail($customerUserUpdateData->userData->email, $user);

        $this->em->flush();

        return $user;
    }

    /**
     * @param int $userId
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUserUpdateData $customerUserUpdateData
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User
     */
    public function editByCustomer($userId, CustomerUserUpdateData $customerUserUpdateData)
    {
        $user = $this->edit($userId, $customerUserUpdateData);

        $this->em->flush();

        return $user;
    }

    /**
     * @param int $userId
     */
    public function delete($userId)
    {
        $user = $this->getUserById($userId);

        $this->em->remove($user);
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User $user
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    public function amendUserDataFromOrder(User $user, Order $order)
    {
        $this->edit(
            $user->getId(),
            $this->customerUserUpdateDataFactory->createAmendedByOrder($user, $order)
        );

        $this->em->flush();
    }

    /**
     * @param string $email
     * @param \Shopsys\FrameworkBundle\Model\Customer\User $user
     */
    protected function setEmail(string $email, User $user): void
    {
        $userByEmailAndDomain = $this->findUserByEmailAndDomain(
            $email,
            $user->getDomainId()
        );

        if ($userByEmailAndDomain !== null && $user->getId() !== $userByEmailAndDomain->getId()) {
            throw new \Shopsys\FrameworkBundle\Model\Customer\Exception\DuplicateEmailException($email);
        }

        $user->setEmail($email);
    }
}
