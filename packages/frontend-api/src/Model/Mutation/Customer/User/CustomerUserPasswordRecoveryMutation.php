<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Customer\User;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\ArgumentFactory;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\Exception\TooManyPasswordRecoveryAttemptsUserError;
use Shopsys\FrontendApiBundle\Model\Mutation\Login\LoginMutation;
use Shopsys\FrontendApiBundle\Model\Security\LoginResultData;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

class CustomerUserPasswordRecoveryMutation extends AbstractMutation
{
    public function __construct(
        protected readonly CustomerUserPasswordFacade $customerUserPasswordFacade,
        protected readonly Domain $domain,
        protected readonly LoginMutation $loginMutation,
        protected readonly ArgumentFactory $argumentFactory,
        protected readonly RateLimiterFactoryInterface $passwordRecoveryIpRateLimiter,
        protected readonly RateLimiterFactoryInterface $passwordRecoveryEmailRateLimiter,
        protected readonly RequestStack $requestStack,
    ) {
    }

    public function requestPasswordRecoveryMutation(Argument $argument, InputValidator $validator): string
    {
        $email = $argument['email'];

        $this->checkPasswordRecoveryRateLimit($email);

        $validator->validate();

        $this->customerUserPasswordFacade->resetPassword($email, $this->domain->getId());

        return 'success';
    }

    public function recoverPasswordMutation(Argument $argument, InputValidator $validator): LoginResultData
    {
        $this->consumeRateLimit($this->passwordRecoveryIpRateLimiter, $this->getClientIp());

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

    protected function checkPasswordRecoveryRateLimit(string $email): void
    {
        $this->consumeRateLimit($this->passwordRecoveryIpRateLimiter, $this->getClientIp());
        $this->consumeRateLimit($this->passwordRecoveryEmailRateLimiter, $this->getEmailRateLimitKeyPart($email));
    }

    protected function getClientIp(): string
    {
        return $this->requestStack->getCurrentRequest()?->getClientIp() ?? 'unknown';
    }

    protected function consumeRateLimit(RateLimiterFactoryInterface $rateLimiterFactory, string $key): void
    {
        if (!$rateLimiterFactory->create($key)->consume()->isAccepted()) {
            throw new TooManyPasswordRecoveryAttemptsUserError('Too many password recovery attempts. Try again later.');
        }
    }

    protected function getEmailRateLimitKeyPart(string $email): string
    {
        return hash('sha256', $this->domain->getId() . ':' . mb_strtolower(trim($email)));
    }
}
