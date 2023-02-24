<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Customer\User;

use GraphQL\Error\UserError;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider;
use Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserDataFactory;
use Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserUpdateDataFactory;
use Shopsys\FrontendApiBundle\Model\Mutation\BaseTokenMutation;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class CustomerUserMutation extends BaseTokenMutation
{
    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider $frontendCustomerUserProvider
     * @param \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $userPasswordHasher
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory
     * @param \Shopsys\FrontendApiBundle\Model\Token\TokenFacade $tokenFacade
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        protected readonly FrontendCustomerUserProvider $frontendCustomerUserProvider,
        protected readonly UserPasswordHasherInterface $userPasswordHasher,
        protected readonly CustomerUserPasswordFacade $customerUserPasswordFacade,
        protected readonly CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade,
        protected readonly CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly CustomerUserDataFactory $customerUserDataFactory,
        protected readonly TokenFacade $tokenFacade,
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
        } catch (UserNotFoundException $e) {
            throw new UserError('This account doesn\'t exist or password is incorrect');
        }

        if (!$this->userPasswordHasher->isPasswordValid($customerUser, $input['oldPassword'])) {
            throw new UserError('This account doesn\'t exist or password is incorrect');
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

        $validator->validate();

        $customerUser = $this->customerUserFacade->getByUuid($user->getUuid());
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromCustomerUserWithArgument(
            $customerUser,
            $argument
        );
        $this->customerUserFacade->editByCustomerUser($customerUser->getId(), $customerUserUpdateData);

        return $customerUser;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return string[]
     */
    public function registerMutation(Argument $argument, InputValidator $validator): array
    {
        $validator->validate();

        $customerUserData = $this->customerUserDataFactory->createWithArgument($argument);
        $customerUser = $this->customerUserFacade->register($customerUserData);

        $deviceId = Uuid::uuid4()->toString();

        return [
            'accessToken' => $this->tokenFacade->createAccessTokenAsString($customerUser, $deviceId),
            'refreshToken' => $this->tokenFacade->createRefreshTokenAsString($customerUser, $deviceId),
        ];
    }
}
