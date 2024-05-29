<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Customer\User;

use Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\CustomerUserMutation as BaseCustomerUserMutation;

/**
 * @property \App\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade
 * @property \App\FrontendApi\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
 * @property \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
 * @method \App\Model\Customer\User\CustomerUser changePassword(\Overblog\GraphQLBundle\Definition\Argument $argument, \Overblog\GraphQLBundle\Validator\InputValidator $validator)
 * @property \App\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
 * @property \App\FrontendApi\Model\Token\TokenFacade $tokenFacade
 * @method \App\Model\Customer\User\CustomerUser changePasswordMutation(\Overblog\GraphQLBundle\Definition\Argument $argument, \Overblog\GraphQLBundle\Validator\InputValidator $validator)
 * @property \App\FrontendApi\Model\Order\OrderApiFacade $orderFacade
 * @method __construct(\Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage, \Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider $frontendCustomerUserProvider, \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $userPasswordHasher, \App\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade, \App\FrontendApi\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory, \App\Model\Customer\User\CustomerUserFacade $customerUserFacade, \Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory, \App\FrontendApi\Model\Token\TokenFacade $tokenFacade, \Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade $productListFacade, \App\Model\Customer\User\RegistrationFacade $registrationFacade, \App\Model\Customer\User\RegistrationDataFactory $registrationDataFactory, \Shopsys\FrontendApiBundle\Model\Cart\MergeCartFacade $mergeCartFacade, \App\FrontendApi\Model\Order\OrderApiFacade $orderFacade, \Shopsys\FrontendApiBundle\Model\Customer\User\RegistrationDataApiFactory $registrationDataApiFactory)
 * @method \App\Model\Customer\User\CustomerUser changePersonalDataMutation(\Overblog\GraphQLBundle\Definition\Argument $argument, \Overblog\GraphQLBundle\Validator\InputValidator $validator)
 * @property \App\Model\Customer\User\RegistrationFacade $registrationFacade
 * @property \App\Model\Customer\User\RegistrationDataFactory $registrationDataApiFactory
 * @property \App\Model\Customer\User\RegistrationDataFactory $registrationDataFactory
 */
class CustomerUserMutation extends BaseCustomerUserMutation
{
}
