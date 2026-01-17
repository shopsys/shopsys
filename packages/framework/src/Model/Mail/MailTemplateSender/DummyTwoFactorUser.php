<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender;

use Override;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;

class DummyTwoFactorUser implements TwoFactorInterface
{
    protected const string DUMMY_EMAIL_AUTH_CODE = 'dummy-email-auth-code';

    public function __construct(protected readonly string $email)
    {
    }

    #[Override]
    public function isEmailAuthEnabled(): bool
    {
        return true;
    }

    #[Override]
    public function getEmailAuthRecipient(): string
    {
        return $this->email;
    }

    #[Override]
    public function getEmailAuthCode(): ?string
    {
        return static::DUMMY_EMAIL_AUTH_CODE;
    }

    #[Override]
    public function setEmailAuthCode(string $authCode): void
    {
    }
}
