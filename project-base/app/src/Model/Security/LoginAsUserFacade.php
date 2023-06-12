<?php

declare(strict_types=1);

namespace App\Model\Security;

use App\FrontendApi\Model\Token\TokenAuthenticator;
use App\FrontendApi\Model\Token\TokenFacade;
use App\Model\Administrator\Administrator;
use App\Model\Administrator\AdministratorFacade;
use App\Model\User\FrontendApi\FrontendApiUser;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository;
use Shopsys\FrameworkBundle\Model\Security\Exception\LoginAsRememberedUserException;
use Shopsys\FrameworkBundle\Model\Security\LoginAsUserFacade as BaseLoginAsUserFacade;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @method rememberLoginAsUser(\App\Model\Customer\User\CustomerUser $customerUser)
 */
class LoginAsUserFacade extends BaseLoginAsUserFacade
{
    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository $customerUserRepository
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade $administratorFrontSecurityFacade
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \App\FrontendApi\Model\Token\TokenAuthenticator $tokenAuthenticator
     * @param \App\Model\Administrator\AdministratorFacade $administratorFacade
     * @param \App\FrontendApi\Model\Token\TokenFacade $tokenFacade
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher,
        CustomerUserRepository $customerUserRepository,
        AdministratorFrontSecurityFacade $administratorFrontSecurityFacade,
        RequestStack $requestStack,
        private readonly TokenAuthenticator $tokenAuthenticator,
        private readonly AdministratorFacade $administratorFacade,
        private readonly TokenFacade $tokenFacade,
    ) {
        parent::__construct(
            $tokenStorage,
            $eventDispatcher,
            $customerUserRepository,
            $administratorFrontSecurityFacade,
            $requestStack,
        );
    }

    /**
     * @param int $customerUserId
     * @return array{accessToken: string, refreshToken: string}
     */
    public function loginAsCustomerUserAndGetAccessAndRefreshToken(int $customerUserId): array
    {
        if (!$this->administratorFrontSecurityFacade->isAdministratorLogged()) {
            throw new LoginAsRememberedUserException('Access denied');
        }

        $deviceId = Uuid::uuid4()->toString();
        /** @var \App\Model\Customer\User\CustomerUser $user */
        $user = $this->customerUserRepository->getCustomerUserById($customerUserId);
        /** @var \App\Model\Administrator\Administrator $administrator */
        $administrator = $this->administratorFrontSecurityFacade->getCurrentAdministrator();

        return [
            'accessToken' => $this->tokenFacade->createAccessTokenAsString($user, $deviceId, $administrator),
            'refreshToken' => $this->tokenFacade->createRefreshTokenAsString($user, $deviceId, $administrator),
        ];
    }

    /**
     * @return \App\Model\Administrator\Administrator|null
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
}
