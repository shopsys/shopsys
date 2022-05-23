<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Login;

use App\FrontendApi\Model\Cart\MergeCartFacade;
use App\FrontendApi\Mutation\Login\Exception\InvalidCredentialsUserError;
use Overblog\GraphQLBundle\Definition\Argument;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider;
use Shopsys\FrontendApiBundle\Model\Mutation\Login\LoginMutation as BaseLoginMutation;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * @property \App\FrontendApi\Model\Token\TokenFacade $tokenFacade
 */
class LoginMutation extends BaseLoginMutation
{
    /**
     * @var \App\FrontendApi\Model\Cart\MergeCartFacade
     */
    private MergeCartFacade $mergeCartFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider $frontendCustomerUserProvider
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $userPasswordEncoder
     * @param \App\FrontendApi\Model\Token\TokenFacade $tokenFacade
     * @param \App\FrontendApi\Model\Cart\MergeCartFacade $mergeCartFacade
     */
    public function __construct(
        FrontendCustomerUserProvider $frontendCustomerUserProvider,
        UserPasswordEncoderInterface $userPasswordEncoder,
        TokenFacade $tokenFacade,
        MergeCartFacade $mergeCartFacade
    ) {
        parent::__construct($frontendCustomerUserProvider, $userPasswordEncoder, $tokenFacade);

        $this->mergeCartFacade = $mergeCartFacade;
    }

    /**
     * {@inheritDoc}
     */
    public function login(Argument $argument): array
    {
        $input = $argument['input'];

        try {
            /** @var \App\Model\Customer\User\CustomerUser $user */
            $user = $this->frontendUserProvider->loadUserByUsername($input['email']);
        } catch (UsernameNotFoundException $e) {
            throw new InvalidCredentialsUserError('Log in failed.');
        }

        if (!$this->userPasswordEncoder->isPasswordValid($user, $input['password'])) {
            throw new InvalidCredentialsUserError('Log in failed.');
        }

        if (array_key_exists('cartUuid', $input) && $input['cartUuid'] !== null) {
            $this->mergeCartFacade->mergeCartByUuidToCustomerCart($input['cartUuid'], $user);
        }

        $deviceId = Uuid::uuid4()->toString();

        return [
            'accessToken' => $this->tokenFacade->createAccessTokenAsString($user, $deviceId),
            'refreshToken' => $this->tokenFacade->createRefreshTokenAsString($user, $deviceId),
        ];
    }
}
