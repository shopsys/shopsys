<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Login;

use App\Component\Deprecation\DeprecatedMethodException;
use App\FrontendApi\Model\Cart\MergeCartFacade;
use App\FrontendApi\Mutation\Login\Exception\InvalidCredentialsUserError;
use Overblog\GraphQLBundle\Definition\Argument;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider;
use Shopsys\FrontendApiBundle\Model\Mutation\Login\LoginMutation as BaseLoginMutation;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

/**
 * @property \App\FrontendApi\Model\Token\TokenFacade $tokenFacade
 */
class LoginMutation extends BaseLoginMutation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider $frontendCustomerUserProvider
     * @param \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $userPasswordHasher
     * @param \App\FrontendApi\Model\Token\TokenFacade $tokenFacade
     * @param \App\FrontendApi\Model\Cart\MergeCartFacade $mergeCartFacade
     */
    public function __construct(
        FrontendCustomerUserProvider $frontendCustomerUserProvider,
        UserPasswordHasherInterface $userPasswordHasher,
        TokenFacade $tokenFacade,
        private readonly MergeCartFacade $mergeCartFacade,
    ) {
        parent::__construct($frontendCustomerUserProvider, $userPasswordHasher, $tokenFacade);
    }

    /**
     * @deprecated Method is deprecated. Use "loginWithResultMutation()" instead.
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return array|string[]
     */
    public function loginMutation(Argument $argument): array
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return array<string, array<string, string>|bool>
     */
    public function loginWithResultMutation(Argument $argument): array
    {
        $input = $argument['input'];

        try {
            /** @var \App\Model\Customer\User\CustomerUser $user */
            $user = $this->frontendCustomerUserProvider->loadUserByUsername($input['email']);
        } catch (UserNotFoundException $e) {
            throw new InvalidCredentialsUserError('Log in failed.');
        }

        if (!$this->userPasswordHasher->isPasswordValid($user, $input['password'])) {
            throw new InvalidCredentialsUserError('Log in failed.');
        }

        if (array_key_exists('cartUuid', $input) && $input['cartUuid'] !== null) {
            $this->mergeCartFacade->mergeCartByUuidToCustomerCart($input['cartUuid'], $user);
        }

        $deviceId = Uuid::uuid4()->toString();

        return [
            'tokens' => [
                'accessToken' => $this->tokenFacade->createAccessTokenAsString($user, $deviceId),
                'refreshToken' => $this->tokenFacade->createRefreshTokenAsString($user, $deviceId),
            ],
            'showCartMergeInfo' => $this->mergeCartFacade->shouldShowCartMergeInfo(),
        ];
    }
}
