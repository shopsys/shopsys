<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security;

use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Router\AdministrationRouter;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Mail\Exception\ResetPasswordHashNotValidException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NewPasswordUrlProvider
{
    public function __construct(
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly AdministrationRouter $administrationRouter,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function getNewPasswordUrl(ResetPasswordInterface $user, int $domainId, string $routeName): string
    {
        if ($user instanceof Administrator) {
            $router = $this->administrationRouter;
        } else {
            $router = $this->domainRouterFactory->getRouter($domainId);
        }

        if (!$user->isResetPasswordHashValid($user->getResetPasswordHash())) {
            throw new ResetPasswordHashNotValidException(sprintf('Reset password mail cannot be sent. %s entity with ID "%d" has invalid reset password hash.', get_class($user), $user->getId()));
        }

        $routeParameters = [
            'email' => $user->getEmail(),
            'hash' => $user->getResetPasswordHash(),
        ];

        return $router->generate(
            $routeName,
            $routeParameters,
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
