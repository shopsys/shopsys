<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Customer\User;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider;
use Shopsys\FrameworkBundle\Model\Customer\User\RegistrationDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\RegistrationFacade;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade;
use Shopsys\FrontendApiBundle\Model\Cart\MergeCartFacade;
use Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserDataFactory;
use Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserUpdateDataFactory;
use Shopsys\FrontendApiBundle\Model\Customer\User\RegistrationDataApiFactory;
use Shopsys\FrontendApiBundle\Model\Mutation\BaseTokenMutation;
use Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\Exception\InvalidAccountOrPasswordUserError;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
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
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\RegistrationFacade $registrationFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\RegistrationDataFactory $registrationDataFactory
     * @param \Shopsys\FrontendApiBundle\Model\Cart\MergeCartFacade $mergeCartFacade
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade $orderFacade
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\RegistrationDataApiFactory $registrationDataApiFactory
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
        protected readonly RegistrationDataApiFactory $registrationDataApiFactory,
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
     * @return array{tokens: array{accessToken: string, refreshToken: string}, showCartMergeInfo: bool}
     */
    public function registerMutation(Argument $argument, InputValidator $validator): array
    {
        $validationGroups = $this->computeValidationGroups($argument);
        $validator->validate($validationGroups);

        $registrationData = $this->registrationDataApiFactory->createWithArgument($argument);
        $customerUser = $this->registrationFacade->register($registrationData);

        if ($argument['input']['cartUuid'] !== null) {
            $this->mergeCartFacade->mergeCartByUuidToCustomerCart($argument['input']['cartUuid'], $customerUser);
        }

        if ($argument['input']['lastOrderUuid'] !== null) {
            $this->orderFacade->pairCustomerUserWithOrderByOrderUuid($customerUser, $argument['input']['lastOrderUuid']);
        }

        $this->productListFacade->mergeProductListsToCustomerUser($argument['input']['productListsUuids'], $customerUser);

        $deviceId = Uuid::uuid4()->toString();

        return [
            'tokens' => [
                'accessToken' => $this->tokenFacade->createAccessTokenAsString($customerUser, $deviceId),
                'refreshToken' => $this->tokenFacade->createRefreshTokenAsString($customerUser, $deviceId),
            ],
            'showCartMergeInfo' => $this->mergeCartFacade->shouldShowCartMergeInfo(),
        ];
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
}
