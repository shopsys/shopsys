<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation;

use Shopsys\FrontendApiBundle\Model\Error\InvalidTokenUserError;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BaseTokenMutation
{
    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return \Shopsys\FrontendApiBundle\Model\User\FrontendApiUser
     */
    protected function runCheckUserIsLogged(): FrontendApiUser
    {
        $token = $this->tokenStorage->getToken();

        if ($token === null) {
            throw new InvalidTokenUserError('Token is not valid.');
        }

        /** @var \Shopsys\FrontendApiBundle\Model\User\FrontendApiUser $user */
        $user = $token->getUser();

        if (!($user instanceof FrontendApiUser)) {
            throw new InvalidTokenUserError('Token is not valid.');
        }

        return $user;
    }
}
