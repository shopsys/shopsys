<?php

namespace Shopsys\FrameworkBundle\Model\Security;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class AdministratorLoginFacade
{
    protected const MULTIDOMAIN_LOGIN_TOKEN_LENGTH = 50;
    protected const MULTIDOMAIN_LOGIN_TOKEN_VALID_SECONDS = 10;

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorRepository $administratorRepository
     * @param \Shopsys\FrameworkBundle\Component\String\HashGenerator $hashGenerator
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly TokenStorageInterface $tokenStorage,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly AdministratorRepository $administratorRepository,
        protected readonly HashGenerator $hashGenerator,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @return string
     */
    public function generateMultidomainLoginTokenWithExpiration(Administrator $administrator)
    {
        $multidomainLoginToken = $this->hashGenerator->generateHash(static::MULTIDOMAIN_LOGIN_TOKEN_LENGTH);
        $multidomainLoginTokenExpirationDateTime = new DateTime(
            '+' . static::MULTIDOMAIN_LOGIN_TOKEN_VALID_SECONDS . 'seconds',
        );
        $administrator->setMultidomainLoginTokenWithExpiration(
            $multidomainLoginToken,
            $multidomainLoginTokenExpirationDateTime,
        );
        $this->em->flush();

        return $multidomainLoginToken;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $multidomainLoginToken
     */
    public function loginByMultidomainToken(Request $request, $multidomainLoginToken)
    {
        $administrator = $this->administratorRepository->getByValidMultidomainLoginToken($multidomainLoginToken);
        $administrator->setMultidomainLogin(true);
        $firewallName = 'administration';
        $token = new UsernamePasswordToken($administrator, $firewallName, $administrator->getRoles());
        $this->tokenStorage->setToken($token);

        $event = new InteractiveLoginEvent($request, $token);
        $this->eventDispatcher->dispatch($event, SecurityEvents::INTERACTIVE_LOGIN);
    }

    public function invalidateCurrentAdministratorLoginToken()
    {
        $token = $this->tokenStorage->getToken();

        if ($token === null) {
            return;
        }

        /** @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator $currentAdministrator */
        $currentAdministrator = $token->getUser();
        $currentAdministrator->setLoginToken('');

        $this->em->flush();
    }
}
