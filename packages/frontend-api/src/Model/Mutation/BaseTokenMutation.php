<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation;

use GraphQL\Error\UserError;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BaseTokenMutation extends AbstractMutation
{
    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     */
    public function __construct(
        protected readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    /**
     * @return \Shopsys\FrontendApiBundle\Model\User\FrontendApiUser
     */
    protected function runCheckUserIsLogged(): FrontendApiUser
    {
        $token = $this->tokenStorage->getToken();

        if ($token === null) {
            throw new UserError('Token is not valid.');
        }

        /** @var \Shopsys\FrontendApiBundle\Model\User\FrontendApiUser $user */
        $user = $token->getUser();

        if (!($user instanceof FrontendApiUser)) {
            throw new UserError('Token is not valid.');
        }

        return $user;
    }
}
