<?php

declare(strict_types=1);

namespace App\Model\Security;

use App\FrontendApi\Model\Token\TokenFacade;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository;
use Shopsys\FrameworkBundle\Model\Security\Exception\LoginAsRememberedUserException;
use Shopsys\FrameworkBundle\Model\Security\LoginAsUserFacade as BaseLoginAsUserFacade;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @method rememberLoginAsUser(\App\Model\Customer\User\CustomerUser $customerUser)
 */
class LoginAsUserFacade extends BaseLoginAsUserFacade
{
    /**
     * @var \App\FrontendApi\Model\Token\TokenFacade
     */
    private TokenFacade $tokenFacade;

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository $customerUserRepository
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade $administratorFrontSecurityFacade
     * @param \App\FrontendApi\Model\Token\TokenFacade $tokenFacade
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher,
        SessionInterface $session,
        CustomerUserRepository $customerUserRepository,
        AdministratorFrontSecurityFacade $administratorFrontSecurityFacade,
        TokenFacade $tokenFacade
    ) {
        parent::__construct(
            $tokenStorage,
            $eventDispatcher,
            $session,
            $customerUserRepository,
            $administratorFrontSecurityFacade
        );

        $this->tokenFacade = $tokenFacade;
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
}
