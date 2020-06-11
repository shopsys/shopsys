<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Customer\User;

use GraphQL\Error\UserError;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class CustomerUserMutation implements MutationInterface, AliasedInterface
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
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider $frontendCustomerUserProvider
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $userPasswordEncoder
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
     */
    public function __construct(
        FrontendCustomerUserProvider $frontendCustomerUserProvider,
        UserPasswordEncoderInterface $userPasswordEncoder,
        CustomerUserPasswordFacade $customerUserPasswordFacade,
        CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
    ) {
        $this->frontendCustomerUserProvider = $frontendCustomerUserProvider;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->customerUserPasswordFacade = $customerUserPasswordFacade;
        $this->customerUserRefreshTokenChainFacade = $customerUserRefreshTokenChainFacade;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function changePassword(Argument $argument, InputValidator $validator): CustomerUser
    {
        $validator->validate();
        $input = $argument['input'];

        try {
            $customerUser = $this->frontendCustomerUserProvider->loadUserByUsername($input['email']);
        } catch (UsernameNotFoundException $e) {
            throw new UserError('User does not exists or provided password is not valid.');
        }

        if (!$this->userPasswordEncoder->isPasswordValid($customerUser, $input['oldPassword'])) {
            throw new UserError('User does not exists or provided password is not valid.');
        }

        $this->customerUserPasswordFacade->changePassword($customerUser, $input['newPassword']);

        return $customerUser;
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'changePassword' => 'customer_user_change_password',
        ];
    }
}
