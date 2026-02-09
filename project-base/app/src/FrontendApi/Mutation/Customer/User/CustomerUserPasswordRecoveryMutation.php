<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Customer\User;

use App\FrontendApi\Mutation\Login\LoginMutation;
use App\Model\Customer\User\CustomerUserPasswordFacade;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\ArgumentFactory;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Security\LoginResultData;

class CustomerUserPasswordRecoveryMutation extends AbstractMutation
{
    public function __construct(
        private readonly CustomerUserPasswordFacade $customerUserPasswordFacade,
        private readonly Domain $domain,
        private readonly LoginMutation $loginMutation,
        private readonly ArgumentFactory $argumentFactory,
    ) {
    }

    public function requestPasswordRecoveryMutation(Argument $argument, InputValidator $validator): string
    {
        $validator->validate();

        $this->customerUserPasswordFacade->resetPassword($argument['email'], $this->domain->getId());

        return 'success';
    }

    public function recoverPasswordMutation(Argument $argument, InputValidator $validator): LoginResultData
    {
        $validator->validate();

        $input = $argument['input'];
        $email = $input['email'];
        $hash = $input['hash'];
        $newPassword = $input['newPassword'];
        $cartUuid = $input['cartUuid'] ?? null;
        $productListsUuids = $input['productListsUuids'] ?? [];
        $shouldOverwriteCustomerUserCart = $input['shouldOverwriteCustomerUserCart'] ?? false;

        $this->customerUserPasswordFacade->setNewPassword($email, $this->domain->getId(), $hash, $newPassword);

        $argumentData = $argument->getArrayCopy();
        $argumentData['input']['password'] = $newPassword;
        $argumentData['input']['cartUuid'] = $cartUuid;
        $argumentData['input']['productListsUuids'] = $productListsUuids;
        $argumentData['input']['shouldOverwriteCustomerUserCart'] = $shouldOverwriteCustomerUserCart;

        /** @var \Overblog\GraphQLBundle\Definition\Argument $newArgument */
        $newArgument = $this->argumentFactory->create($argumentData);

        return $this->loginMutation->loginMutation($newArgument);
    }
}
