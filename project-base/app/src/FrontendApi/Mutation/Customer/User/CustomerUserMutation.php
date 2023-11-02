<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Customer\User;

use App\FrontendApi\Model\Cart\MergeCartFacade;
use App\FrontendApi\Model\Order\OrderApiFacade;
use App\Model\Customer\User\RegistrationDataFactoryInterface;
use App\Model\Customer\User\RegistrationFacadeInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider;
use Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserDataFactory;
use Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserUpdateDataFactory;
use Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\CustomerUserMutation as BaseCustomerUserMutation;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @property \App\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade
 * @property \App\FrontendApi\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
 * @property \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
 * @method \App\Model\Customer\User\CustomerUser changePassword(\Overblog\GraphQLBundle\Definition\Argument $argument, \Overblog\GraphQLBundle\Validator\InputValidator $validator)
 * @property \App\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
 * @property \App\FrontendApi\Model\Token\TokenFacade $tokenFacade
 * @method \App\Model\Customer\User\CustomerUser changePasswordMutation(\Overblog\GraphQLBundle\Definition\Argument $argument, \Overblog\GraphQLBundle\Validator\InputValidator $validator)
 */
class CustomerUserMutation extends BaseCustomerUserMutation
{
    public const VALIDATION_GROUP_COMPANY_CUSTOMER = 'companyCustomer';

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider $frontendCustomerUserProvider
     * @param \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $userPasswordHasher
     * @param \App\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade
     * @param \App\FrontendApi\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
     * @param \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory
     * @param \App\FrontendApi\Model\Token\TokenFacade $tokenFacade
     * @param \App\Model\Customer\User\RegistrationFacadeInterface $registrationFacade
     * @param \App\Model\Customer\User\RegistrationDataFactoryInterface $registrationDataFactory
     * @param \App\FrontendApi\Model\Cart\MergeCartFacade $mergeCartFacade
     * @param \App\FrontendApi\Model\Order\OrderApiFacade $orderFacade
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        FrontendCustomerUserProvider $frontendCustomerUserProvider,
        UserPasswordHasherInterface $userPasswordHasher,
        CustomerUserPasswordFacade $customerUserPasswordFacade,
        CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        CustomerUserFacade $customerUserFacade,
        CustomerUserDataFactory $customerUserDataFactory,
        TokenFacade $tokenFacade,
        private readonly RegistrationFacadeInterface $registrationFacade,
        private readonly RegistrationDataFactoryInterface $registrationDataFactory,
        private readonly MergeCartFacade $mergeCartFacade,
        private readonly OrderApiFacade $orderFacade,
    ) {
        parent::__construct(
            $tokenStorage,
            $frontendCustomerUserProvider,
            $userPasswordHasher,
            $customerUserPasswordFacade,
            $customerUserUpdateDataFactory,
            $customerUserFacade,
            $customerUserDataFactory,
            $tokenFacade,
        );
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return array
     */
    public function registerMutation(Argument $argument, InputValidator $validator): array
    {
        $validationGroups = $this->computeValidationGroups($argument);
        $validator->validate($validationGroups);

        $registrationData = $this->registrationDataFactory->createWithArgument($argument);
        $customerUser = $this->registrationFacade->register($registrationData);

        if ($argument['input']['cartUuid'] !== null) {
            $this->mergeCartFacade->mergeCartByUuidToCustomerCart($argument['input']['cartUuid'], $customerUser);
        }

        if ($argument['input']['lastOrderUuid'] !== null) {
            $this->orderFacade->pairCustomerUserWithOrderByOrderUuid($customerUser, $argument['input']['lastOrderUuid']);
        }

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
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \App\Model\Customer\User\CustomerUser
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
     * @return string[]
     */
    private function computeValidationGroups(Argument $argument): array
    {
        $input = $argument['input'];
        $validationGroups = ['Default'];

        if ($input[self::VALIDATION_GROUP_COMPANY_CUSTOMER] === true) {
            $validationGroups[] = self::VALIDATION_GROUP_COMPANY_CUSTOMER;
        }

        return $validationGroups;
    }
}
