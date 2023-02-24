<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Login;

use Overblog\GraphQLBundle\Definition\Argument;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\Exception\InvalidAccountOrPasswordUserError;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class LoginMutation extends AbstractMutation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider $frontendCustomerUserProvider
     * @param \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $userPasswordHasher
     * @param \Shopsys\FrontendApiBundle\Model\Token\TokenFacade $tokenFacade
     */
    public function __construct(
        protected readonly FrontendCustomerUserProvider $frontendCustomerUserProvider,
        protected readonly UserPasswordHasherInterface $userPasswordHasher,
        protected readonly TokenFacade $tokenFacade
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return string[]
     */
    public function loginMutation(Argument $argument): array
    {
        $input = $argument['input'];

        try {
            $user = $this->frontendCustomerUserProvider->loadUserByUsername($input['email']);
        } catch (UserNotFoundException $e) {
            throw new InvalidAccountOrPasswordUserError($e->getMessage());
        }

        if (!$this->userPasswordHasher->isPasswordValid($user, $input['password'])) {
            throw new InvalidAccountOrPasswordUserError('Invalid password.');
        }

        $deviceId = Uuid::uuid4()->toString();

        return [
            'accessToken' => $this->tokenFacade->createAccessTokenAsString($user, $deviceId),
            'refreshToken' => $this->tokenFacade->createRefreshTokenAsString($user, $deviceId),
        ];
    }
}
