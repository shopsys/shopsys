<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Customer\User;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupFacade;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade;
use Shopsys\FrontendApiBundle\Model\Cart\MergeCartFacade;
use Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserDataFactory;
use Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserUpdateDataFactory;
use Shopsys\FrontendApiBundle\Model\Customer\User\RegistrationDataFactory;
use Shopsys\FrontendApiBundle\Model\Customer\User\RegistrationFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\BaseTokenMutation;
use Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\Exception\CannotDeleteOwnCustomerUserError;
use Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\Exception\CustomerUserNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\Exception\InvalidAccountOrPasswordUserError;
use Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\Exception\LastCustomerUserWithDefaultRoleGroupError;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Shopsys\FrontendApiBundle\Model\Security\LoginResultData;
use Shopsys\FrontendApiBundle\Model\Security\LoginResultDataFactory;
use Shopsys\FrontendApiBundle\Model\Security\TokensDataFactory;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class CustomerUserMutation extends BaseTokenMutation
{
    protected const string VALIDATION_GROUP_COMPANY_CUSTOMER = 'companyCustomer';

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider $frontendCustomerUserProvider
     * @param \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $userPasswordHasher
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory
     * @param \Shopsys\FrontendApiBundle\Model\Token\TokenFacade $tokenFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade $productListFacade
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\RegistrationFacade $registrationFacade
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\RegistrationDataFactory $registrationDataFactory
     * @param \Shopsys\FrontendApiBundle\Model\Cart\MergeCartFacade $mergeCartFacade
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade $orderFacade
     * @param \Shopsys\FrontendApiBundle\Model\Security\LoginResultDataFactory $loginResultDataFactory
     * @param \Shopsys\FrontendApiBundle\Model\Security\TokensDataFactory $tokensDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupFacade $customerUserRoleGroupFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade $customerFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        protected readonly FrontendCustomerUserProvider $frontendCustomerUserProvider,
        protected readonly UserPasswordHasherInterface $userPasswordHasher,
        protected readonly CustomerUserPasswordFacade $customerUserPasswordFacade,
        protected readonly CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly CustomerUserDataFactory $customerUserDataFactory,
        protected readonly TokenFacade $tokenFacade,
        protected readonly ProductListFacade $productListFacade,
        protected readonly RegistrationFacade $registrationFacade,
        protected readonly RegistrationDataFactory $registrationDataFactory,
        protected readonly MergeCartFacade $mergeCartFacade,
        protected readonly OrderApiFacade $orderFacade,
        protected readonly LoginResultDataFactory $loginResultDataFactory,
        protected readonly TokensDataFactory $tokensDataFactory,
        protected readonly CustomerUserRoleGroupFacade $customerUserRoleGroupFacade,
        protected readonly CustomerFacade $customerFacade,
        protected readonly Domain $domain,
    ) {
        parent::__construct($tokenStorage);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function changePasswordMutation(Argument $argument, InputValidator $validator): CustomerUser
    {
        $this->runCheckUserIsLogged();

        $validator->validate();
        $input = $argument['input'];

        try {
            $customerUser = $this->frontendCustomerUserProvider->loadUserByUsername($input['email']);
        } catch (UserNotFoundException) {
            throw new InvalidAccountOrPasswordUserError('This account doesn\'t exist or password is incorrect');
        }

        if (!$this->userPasswordHasher->isPasswordValid($customerUser, $input['oldPassword'])) {
            throw new InvalidAccountOrPasswordUserError('This account doesn\'t exist or password is incorrect');
        }

        $this->customerUserPasswordFacade->changePassword($customerUser, $input['newPassword']);

        return $customerUser;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function changePersonalDataMutation(Argument $argument, InputValidator $validator): CustomerUser
    {
        $user = $this->runCheckUserIsLogged();

        $validationGroups = $this->computeValidationGroups($argument);
        $validator->validate($validationGroups);

        $customerUser = $this->customerUserFacade->getByUuid($user->getUuid());
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromCustomerUserWithArgument(
            $customerUser,
            $argument,
        );
        $this->customerUserFacade->editByCustomerUser($customerUser->getId(), $customerUserUpdateData);

        return $customerUser;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \Shopsys\FrontendApiBundle\Model\Security\LoginResultData
     */
    public function registerMutation(Argument $argument, InputValidator $validator): LoginResultData
    {
        $validationGroups = $this->computeValidationGroups($argument);
        $validator->validate($validationGroups);

        $registrationData = $this->registrationDataFactory->createWithArgument($argument);
        $customerUser = $this->registrationFacade->register($registrationData);

        if ($argument['input']['cartUuid'] !== null) {
            $this->mergeCartFacade->mergeCartByUuidToCustomerCart($argument['input']['cartUuid'], $customerUser);
        }

        if ($argument['input']['lastOrderUuid'] !== null) {
            $this->orderFacade->pairCustomerUserWithOrderByOrderUuid($customerUser, $argument['input']['lastOrderUuid']);
        }

        $this->productListFacade->mergeProductListsToCustomerUser($argument['input']['productListsUuids'], $customerUser);

        $deviceId = Uuid::uuid4()->toString();

        return $this->loginResultDataFactory->create(
            $this->tokensDataFactory->create(
                $this->tokenFacade->createAccessTokenAsString($customerUser, $deviceId),
                $this->tokenFacade->createRefreshTokenAsString($customerUser, $deviceId),
            ),
            $this->mergeCartFacade->shouldShowCartMergeInfo(),
        );
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return string[]
     */
    protected function computeValidationGroups(Argument $argument): array
    {
        $input = $argument['input'];
        $validationGroups = ['Default'];

        if ($input[self::VALIDATION_GROUP_COMPANY_CUSTOMER] === true) {
            $validationGroups[] = self::VALIDATION_GROUP_COMPANY_CUSTOMER;
        }

        return $validationGroups;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function addNewCustomerUserMutation(Argument $argument, InputValidator $validator): CustomerUser
    {
        $user = $this->runCheckUserIsLogged();

        $validator->validate();

        $customerUser = $this->customerUserFacade->getByUuid($user->getUuid());
        $customer = $customerUser->getCustomer();
        $customerUserData = $this->customerUserDataFactory->createNewForCustomerWithArgument($customer, $argument);

        return $this->customerUserFacade->createCustomerUserWithActivationMail($customer, $customerUserData);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function editCustomerUserPersonalDataMutation(Argument $argument, InputValidator $validator): CustomerUser
    {
        $this->runCheckUserIsLogged();

        $validator->validate();
        $input = $argument['input'];

        try {
            $customerUser = $this->customerUserFacade->getByUuid($input['customerUserUuid']);
        } catch (CustomerUserNotFoundException $exception) {
            throw new CustomerUserNotFoundUserError('Customer user with uuid ' . $input['customerUserUuid'] . ' not found');
        }
        $customerUserData = $this->customerUserDataFactory->createForCustomerUserWithArgument($customerUser, $argument);

        return $this->customerUserFacade->editCustomerUser($customerUser->getId(), $customerUserData);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return bool
     */
    public function removeCustomerUserMutation(Argument $argument): bool
    {
        $frontendApiUser = $this->runCheckUserIsLogged();

        $input = $argument['input'];

        try {
            $currentUser = $this->customerUserFacade->getByUuid($frontendApiUser->getUuid());
            $customerUser = $this->customerUserFacade->getByUuid($input['customerUserUuid']);
        } catch (CustomerUserNotFoundException $exception) {
            throw new CustomerUserNotFoundUserError('Customer user with uuid ' . $input['customerUserUuid'] . ' not found');
        }

        $this->checkCustomerUserCanBeDeleted($customerUser, $currentUser);

        $this->customerUserFacade->delete($customerUser->getId());

        return true;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUserToDelete
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $currentCustomer
     */
    protected function checkCustomerUserCanBeDeleted(
        CustomerUser $customerUserToDelete,
        CustomerUser $currentCustomer,
    ): void {
        if ($currentCustomer->getUuid() === $customerUserToDelete->getUuid()) {
            throw new CannotDeleteOwnCustomerUserError('Cannot delete own user');
        }

        if (!$customerUserToDelete->getCustomer()->getBillingAddress()->isCompanyCustomer()) {
            return;
        }

        $defaultCustomerRoleGroup = $this->customerUserRoleGroupFacade->getDefaultCustomerUserRoleGroup();

        if ($customerUserToDelete->getRoleGroup()->getId() !== $defaultCustomerRoleGroup->getId()) {
            return;
        }

        $customer = $customerUserToDelete->getCustomer();
        $customerUsers = $this->customerFacade->getCustomerUsers($customer);
        $customerUsersWithDefaultRoleGroup = [];

        foreach ($customerUsers as $otherCustomerUser) {
            if ($otherCustomerUser->getRoleGroup()->getId() === $defaultCustomerRoleGroup->getId()) {
                $customerUsersWithDefaultRoleGroup[] = $otherCustomerUser;
            }
        }

        if (count($customerUsersWithDefaultRoleGroup) === 1) {
            throw new LastCustomerUserWithDefaultRoleGroupError(
                'Customer user with uuid ' . $customerUserToDelete->getUuid() . ' is the last customer user with default role group.',
            );
        }
    }
}
