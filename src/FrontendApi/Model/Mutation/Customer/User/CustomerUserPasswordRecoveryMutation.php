<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Mutation\Customer\User;

use App\Model\Customer\User\CustomerUserPasswordFacade;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\ArgumentFactory;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundByEmailAndDomainException;
use Shopsys\FrontendApiBundle\Model\Mutation\Login\LoginMutation;

class CustomerUserPasswordRecoveryMutation implements MutationInterface, AliasedInterface
{
    /**
     * @var \App\Model\Customer\User\CustomerUserPasswordFacade
     */
    private CustomerUserPasswordFacade $customerUserPasswordFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Mutation\Login\LoginMutation
     */
    private LoginMutation $loginMutation;

    /**
     * @var \Overblog\GraphQLBundle\Definition\ArgumentFactory
     */
    private ArgumentFactory $argumentFactory;

    /**
     * @param \App\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\Mutation\Login\LoginMutation $loginMutation
     * @param \Overblog\GraphQLBundle\Definition\ArgumentFactory $argumentFactory
     */
    public function __construct(
        CustomerUserPasswordFacade $customerUserPasswordFacade,
        Domain $domain,
        LoginMutation $loginMutation,
        ArgumentFactory $argumentFactory
    ) {
        $this->customerUserPasswordFacade = $customerUserPasswordFacade;
        $this->domain = $domain;
        $this->loginMutation = $loginMutation;
        $this->argumentFactory = $argumentFactory;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return string
     */
    public function requestPasswordRecovery(Argument $argument, InputValidator $validator): string
    {
        $validator->validate();

        try {
            $this->customerUserPasswordFacade->resetPassword($argument['email'], $this->domain->getId());

            return 'success';
        } catch (CustomerUserNotFoundByEmailAndDomainException $ex) {
            throw new UserError('User with provided email address does not exists.');
        }
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return array
     */
    public function recoverPassword(Argument $argument, InputValidator $validator): array
    {
        $validator->validate();

        $input = $argument['input'];
        $email = $input['email'];
        $hash = $input['hash'];
        $newPassword = $input['newPassword'];

        if (!$this->customerUserPasswordFacade->isResetPasswordHashValid($email, $this->domain->getId(), $hash)) {
            throw new UserError('Provided hash is not valid.');
        }

        $this->customerUserPasswordFacade->setNewPassword($email, $this->domain->getId(), $hash, $newPassword);

        $argumentData = $argument->getArrayCopy();
        $argumentData['input']['password'] = $newPassword;

        /** @var \Overblog\GraphQLBundle\Definition\Argument $newArgument */
        $newArgument = $this->argumentFactory->create($argumentData);

        return $this->loginMutation->login($newArgument);
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'requestPasswordRecovery' => 'requestPasswordRecovery',
            'recoverPassword' => 'recoverPassword',
        ];
    }
}
