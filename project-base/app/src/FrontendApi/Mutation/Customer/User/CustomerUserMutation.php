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
 * @method \App\Model\Customer\User\CustomerUser changePersonalDataMutation(\Overblog\GraphQLBundle\Definition\Argument $argument, \Overblog\GraphQLBundle\Validator\InputValidator $validator)
 * @method __construct(\Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage, \Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider $frontendCustomerUserProvider, \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $userPasswordHasher, \App\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade, \App\FrontendApi\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory, \App\Model\Customer\User\CustomerUserFacade $customerUserFacade, \Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory, \App\FrontendApi\Model\Token\TokenFacade $tokenFacade, \Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade $productListFacade, \Shopsys\FrontendApiBundle\Model\Customer\User\RegistrationFacade $registrationFacade, \Shopsys\FrontendApiBundle\Model\Customer\User\RegistrationDataFactory $registrationDataFactory, \Shopsys\FrontendApiBundle\Model\Cart\MergeCartFacade $mergeCartFacade, \App\FrontendApi\Model\Order\OrderApiFacade $orderFacade, \Shopsys\FrontendApiBundle\Model\Security\LoginResultDataFactory $loginResultDataFactory, \Shopsys\FrontendApiBundle\Model\Security\TokensDataFactory $tokensDataFactory, \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupFacade $customerUserRoleGroupFacade, \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade $customerFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain)
 * @method \App\Model\Customer\User\CustomerUser addNewCustomerUserMutation(\Overblog\GraphQLBundle\Definition\Argument $argument, \Overblog\GraphQLBundle\Validator\InputValidator $validator)
 * @method \App\Model\Customer\User\CustomerUser editCustomerUserMutation(\Overblog\GraphQLBundle\Definition\Argument $argument, \Overblog\GraphQLBundle\Validator\InputValidator $validator)
 * @method \App\Model\Customer\User\CustomerUser editCustomerUserPersonalDataMutation(\Overblog\GraphQLBundle\Definition\Argument $argument, \Overblog\GraphQLBundle\Validator\InputValidator $validator)
 * @method checkCustomerUserCanBeDeleted(\App\Model\Customer\User\CustomerUser $customerUserToDelete, \App\Model\Customer\User\CustomerUser $currentCustomer)
 */
class CustomerUserMutation extends BaseCustomerUserMutation
{
}
