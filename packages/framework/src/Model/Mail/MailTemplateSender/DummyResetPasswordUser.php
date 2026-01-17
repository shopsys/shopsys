<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender;

use Override;
use Shopsys\FrameworkBundle\Component\Security\ResetPasswordInterface;

class DummyResetPasswordUser implements ResetPasswordInterface
{
    protected const string DUMMY_RESET_PASSWORD_HASH = 'dummy-reset-password-hash';

    public function __construct(protected readonly string $email)
    {
    }

    #[Override]
    public function getId()
    {
        return 1;
    }

    #[Override]
    public function isResetPasswordHashValid(?string $hash): bool
    {
        return true;
    }

    #[Override]
    public function getResetPasswordHash()
    {
        return static::DUMMY_RESET_PASSWORD_HASH;
    }

    #[Override]
    public function getEmail()
    {
        return $this->email;
    }
}
