<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security;

use Shopsys\FrameworkBundle\Component\Router\AdministrationRouter;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Model\Mail\Exception\ResetPasswordHashNotValidException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NewPasswordUrlProvider
{
    public function __construct(
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly AdministrationRouter $administrationRouter,
    ) {
    }

    public function getNewPasswordUrl(ResetPasswordInterface $user, int $domainId, string $routeName): string
    {
        if ($this->administrationRouter->getRouteCollection()->get($routeName) !== null) {
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
