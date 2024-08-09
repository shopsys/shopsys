<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Voter;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

abstract class AbstractB2bVoter extends Voter
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(protected readonly Domain $domain)
    {
    }

    /**
     * @param string $attribute
     * @param array $subject
     */
    abstract protected function supports(string $attribute, $subject);

    /**
     * @param string $attribute
     * @param \Overblog\GraphQLBundle\Definition\Argument|null $argument
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     */
    abstract protected function checkAccess(string $attribute, ?Argument $argument, TokenInterface $token);

    /**
     * @param string $attribute
     * @param mixed $subject
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        if ($this->domain->isB2b() === false) {
            return false;
        }

        if ($subject !== null && !$subject instanceof Argument) {
            throw new UnexpectedTypeException($subject, Argument::class);
        }

        $argument = $subject;

        return $this->checkAccess($attribute, $argument, $token);
    }
}
