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

class CustomerUserPasswordRecoveryMutation extends AbstractMutation
{
    /**
     * @param \App\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\FrontendApi\Mutation\Login\LoginMutation $loginMutation
     * @param \Overblog\GraphQLBundle\Definition\ArgumentFactory $argumentFactory
     */
    public function __construct(
        private readonly CustomerUserPasswordFacade $customerUserPasswordFacade,
        private readonly Domain $domain,
        private readonly LoginMutation $loginMutation,
        private readonly ArgumentFactory $argumentFactory
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return string
     */
    public function requestPasswordRecoveryMutation(Argument $argument, InputValidator $validator): string
    {
        $validator->validate();

        $this->customerUserPasswordFacade->resetPassword($argument['email'], $this->domain->getId());

        return 'success';
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return array
     */
    public function recoverPasswordMutation(Argument $argument, InputValidator $validator): array
    {
        $validator->validate();

        $input = $argument['input'];
        $email = $input['email'];
        $hash = $input['hash'];
        $newPassword = $input['newPassword'];

        $this->customerUserPasswordFacade->setNewPassword($email, $this->domain->getId(), $hash, $newPassword);

        $argumentData = $argument->getArrayCopy();
        $argumentData['input']['password'] = $newPassword;

        /** @var \Overblog\GraphQLBundle\Definition\Argument $newArgument */
        $newArgument = $this->argumentFactory->create($argumentData);

        return $this->loginMutation->loginWithResultMutation($newArgument);
    }
}
