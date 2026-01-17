<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Voter;

use Overblog\GraphQLBundle\Definition\Argument;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

abstract class AbstractB2bVoter extends Voter
{
    public function __construct(protected readonly Domain $domain)
    {
    }

    /**
     * @param array $subject
     */
    #[Override]
    abstract protected function supports(string $attribute, $subject): bool;

    abstract protected function checkAccess(string $attribute, ?Argument $argument, TokenInterface $token);

    /**
     * @param mixed $subject
     */
    #[Override]
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
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
