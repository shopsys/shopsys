<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Customer\User;

use GraphQL\Error\UserError;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class CustomerUserMutation extends BaseTokenMutation implements MutationInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider
     */
    protected $frontendCustomerUserProvider;

    /**
     * @var \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface
     */
    protected $userPasswordEncoder;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade
     */
    protected $customerUserPasswordFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade
     */
    protected $customerUserRefreshTokenChainFacade;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserUpdateDataFactory
     */
    protected $customerUserUpdateDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade
     */
    protected $customerUserFacade;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserDataFactory
     */
    protected $customerUserDataFactory;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Token\TokenFacade
     */
    protected $tokenFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider $frontendCustomerUserProvider
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $userPasswordEncoder
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory
     * @param \Shopsys\FrontendApiBundle\Model\Token\TokenFacade $tokenFacade
     */
    public function __construct(
        FrontendCustomerUserProvider $frontendCustomerUserProvider,
        UserPasswordEncoderInterface $userPasswordEncoder,
        CustomerUserPasswordFacade $customerUserPasswordFacade,
        CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade,
        TokenStorageInterface $tokenStorage,
        CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        CustomerUserFacade $customerUserFacade,
        CustomerUserDataFactory $customerUserDataFactory,
        TokenFacade $tokenFacade
    ) {
        parent::__construct($tokenStorage);

        $this->frontendCustomerUserProvider = $frontendCustomerUserProvider;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->customerUserPasswordFacade = $customerUserPasswordFacade;
        $this->customerUserRefreshTokenChainFacade = $customerUserRefreshTokenChainFacade;
        $this->customerUserUpdateDataFactory = $customerUserUpdateDataFactory;
        $this->customerUserFacade = $customerUserFacade;
        $this->customerUserDataFactory = $customerUserDataFactory;
        $this->tokenFacade = $tokenFacade;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function changePassword(Argument $argument, InputValidator $validator): CustomerUser
    {
        $this->runCheckUserIsLogged();

        $validator->validate();
        $input = $argument['input'];

        try {
            $customerUser = $this->frontendCustomerUserProvider->loadUserByUsername($input['email']);
        } catch (UsernameNotFoundException $e) {
            throw new UserError('This account doesn\'t exist or password is incorrect');
        }

        if (!$this->userPasswordEncoder->isPasswordValid($customerUser, $input['oldPassword'])) {
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
    public function changePersonalData(Argument $argument, InputValidator $validator): CustomerUser
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
    public function register(Argument $argument, InputValidator $validator): array
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

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'changePassword' => 'customer_user_change_password',
            'changePersonalData' => 'customer_user_change_personal_data',
            'register' => 'customer_user_register',
        ];
    }
}
