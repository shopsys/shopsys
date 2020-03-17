<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Login;

use GraphQL\Error\UserError;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class LoginMutation implements MutationInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider
     */
    protected $frontendUserProvider;

    /**
     * @var \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface
     */
    protected $userPasswordEncoder;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Token\TokenFacade
     */
    protected $tokenFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider $frontendCustomerUserProvider
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $userPasswordEncoder
     * @param \Shopsys\FrontendApiBundle\Model\Token\TokenFacade $tokenFacade
     */
    public function __construct(
        FrontendCustomerUserProvider $frontendCustomerUserProvider,
        UserPasswordEncoderInterface $userPasswordEncoder,
        TokenFacade $tokenFacade
    ) {
        $this->frontendUserProvider = $frontendCustomerUserProvider;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->tokenFacade = $tokenFacade;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return string[]
     */
    public function login(Argument $argument): array
    {
        $input = $argument['input'];

        try {
            $user = $this->frontendUserProvider->loadUserByUsername($input['email']);
        } catch (UsernameNotFoundException $e) {
            throw new UserError('Log in failed.');
        }

        if (!$this->userPasswordEncoder->isPasswordValid($user, $input['password'])) {
            throw new UserError('Log in failed.');
        }

        return [
            'accessToken' => $this->tokenFacade->generateAccessTokenByCustomerUser($user),
        ];
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'login' => 'user_login',
        ];
    }
}
