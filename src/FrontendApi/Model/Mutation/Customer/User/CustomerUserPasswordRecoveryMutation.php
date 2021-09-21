<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Mutation\Customer\User;

use App\Model\Customer\User\CustomerUserPasswordFacade;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundByEmailAndDomainException;

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
     * @param \App\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(CustomerUserPasswordFacade $customerUserPasswordFacade, Domain $domain)
    {
        $this->customerUserPasswordFacade = $customerUserPasswordFacade;
        $this->domain = $domain;
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
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'requestPasswordRecovery' => 'requestPasswordRecovery',
        ];
    }
}
