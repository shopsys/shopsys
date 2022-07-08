<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use Shopsys\FrameworkBundle\Model\Mail\Exception\MasterMailNotSetException;

class MailerSettingProvider
{
    /**
     * @var string[]
     */
    protected array $mailerWhitelistExpressions;

    /**
     * @var string|null
     */
    protected ?string $mailerMasterEmailAddress;

    /**
     * @var bool
     */
    protected bool $deliveryDisabled;

    /**
     * @param string $mailerWhitelist
     * @param string $mailerMasterEmailAddress
     * @param string $mailerDsn
     */
    public function __construct(
        string $mailerWhitelist,
        string $mailerMasterEmailAddress,
        string $mailerDsn
    ) {
        $this->mailerWhitelistExpressions = $mailerWhitelist !== '' ? explode(',', $mailerWhitelist) : [];
        $this->mailerMasterEmailAddress = $mailerMasterEmailAddress !== '' ? $mailerMasterEmailAddress : null;
        $this->deliveryDisabled = $mailerDsn === Mailer::DISABLED_MAILER_DSN;
    }

    /**
     * @return string[]
     */
    public function getMailerWhitelistExpressions(): array
    {
        return $this->mailerWhitelistExpressions;
    }

    /**
     * @return string
     */
    public function getMailerMasterEmailAddress(): string
    {
        if ($this->isMailerMasterEmailSet() === false) {
            throw new MasterMailNotSetException();
        }

        return $this->mailerMasterEmailAddress;
    }

    /**
     * @return bool
     */
    public function isDeliveryDisabled(): bool
    {
        return $this->deliveryDisabled;
    }

    /**
     * @return bool
     */
    public function isMailerMasterEmailSet(): bool
    {
        return $this->mailerMasterEmailAddress !== null && $this->mailerMasterEmailAddress !== '';
    }
}
