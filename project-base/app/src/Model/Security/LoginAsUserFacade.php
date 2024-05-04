<?php

declare(strict_types=1);

namespace App\Model\Security;

use Shopsys\FrameworkBundle\Model\Security\LoginAsUserFacade as BaseLoginAsUserFacade;

/**
 * @property \App\FrontendApi\Model\Token\TokenAuthenticator $tokenAuthenticator
 * @property \App\Model\Administrator\AdministratorFacade $administratorFacade
 * @property \App\FrontendApi\Model\Token\TokenFacade $tokenFacade
 * @method __construct(\Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage, \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher, \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository $customerUserRepository, \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade $administratorFrontSecurityFacade, \Symfony\Component\HttpFoundation\RequestStack $requestStack, \App\FrontendApi\Model\Token\TokenAuthenticator $tokenAuthenticator, \App\Model\Administrator\AdministratorFacade $administratorFacade, \App\FrontendApi\Model\Token\TokenFacade $tokenFacade)
 * @method \App\Model\Administrator\Administrator|null getCurrentAdministratorLoggedAsCustomer()
 * @method array{accessToken: string, refreshToken: string} loginAndReturnAccessAndRefreshToken(\App\Model\Customer\User\CustomerUser $customerUser)
 */
class LoginAsUserFacade extends BaseLoginAsUserFacade
{
}
