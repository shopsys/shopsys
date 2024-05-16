<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security;

use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository;
use Shopsys\FrameworkBundle\Model\Security\Exception\LoginAsRememberedUserException;
use Shopsys\FrontendApiBundle\Model\Token\TokenAuthenticator;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUser;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LoginAsUserFacade
{
    public const SESSION_LOGIN_AS = 'loginAsUser';

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository $customerUserRepository
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade $administratorFrontSecurityFacade
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Shopsys\FrontendApiBundle\Model\Token\TokenAuthenticator $tokenAuthenticator
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade $administratorFacade
     * @param \Shopsys\FrontendApiBundle\Model\Token\TokenFacade $tokenFacade
     */
    public function __construct(
        protected readonly TokenStorageInterface $tokenStorage,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly CustomerUserRepository $customerUserRepository,
        protected readonly AdministratorFrontSecurityFacade $administratorFrontSecurityFacade,
        protected readonly RequestStack $requestStack,
        protected readonly TokenAuthenticator $tokenAuthenticator,
        protected readonly AdministratorFacade $administratorFacade,
        protected readonly TokenFacade $tokenFacade,
    ) {
    }

    /**
     * @param int $customerUserId
     * @return array{accessToken: string, refreshToken: string}
     */
    public function loginAdministratorAsCustomerUserAndGetAccessAndRefreshToken(int $customerUserId): array
    {
        if (!$this->administratorFrontSecurityFacade->isAdministratorLogged()) {
            throw new LoginAsRememberedUserException('Access denied');
        }

        $deviceId = Uuid::uuid4()->toString();
        $user = $this->customerUserRepository->getCustomerUserById($customerUserId);
        $administrator = $this->administratorFrontSecurityFacade->getCurrentAdministrator();

        return [
            'accessToken' => $this->tokenFacade->createAccessTokenAsString($user, $deviceId, $administrator),
            'refreshToken' => $this->tokenFacade->createRefreshTokenAsString($user, $deviceId, $administrator),
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null
     */
    public function getCurrentAdministratorLoggedAsCustomer(): ?Administrator
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            return null;
        }
        $tokenString = $this->tokenAuthenticator->getCredentials($request);

        if ($tokenString === null) {
            return null;
        }
        $unencryptedToken = $this->tokenFacade->getTokenByString($tokenString);
        $claims = $unencryptedToken->claims();
        $administratorUuid = $claims->get(FrontendApiUser::CLAIM_ADMINISTRATOR_UUID);

        if ($administratorUuid === null) {
            return null;
        }

        return $this->administratorFacade->findByUuid($administratorUuid);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return array{accessToken: string, refreshToken: string}
     */
    public function loginAndReturnAccessAndRefreshToken(CustomerUser $customerUser): array
    {
        $deviceId = Uuid::uuid4()->toString();

        return [
            'accessToken' => $this->tokenFacade->createAccessTokenAsString($customerUser, $deviceId),
            'refreshToken' => $this->tokenFacade->createRefreshTokenAsString($customerUser, $deviceId),
        ];
    }
}
