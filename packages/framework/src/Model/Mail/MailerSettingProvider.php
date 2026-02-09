<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use Nette\Utils\Json;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade;

class MailerSettingProvider
{
    protected bool $deliveryDisabled;

    public function __construct(
        string $mailerDsn,
        protected readonly bool $whitelistForced,
        protected readonly MailSettingFacade $mailSettingFacade,
    ) {
        $this->deliveryDisabled = $mailerDsn === Mailer::DISABLED_MAILER_DSN;
    }

    /**
     * @return string[]
     */
    public function getWhitelistPatternsAsArray(int $domainId): array
    {
        $mailWhitelist = $this->mailSettingFacade->getMailWhitelist($domainId);

        return $mailWhitelist !== null ? Json::decode($mailWhitelist, true) : [];
    }

    public function isWhitelistForced(): bool
    {
        return $this->whitelistForced;
    }

    public function isWhitelistEnabled(int $domainId): bool
    {
        return $this->isWhitelistForced() || $this->mailSettingFacade->isWhitelistEnabled($domainId);
    }

    public function isDeliveryDisabled(): bool
    {
        return $this->deliveryDisabled;
    }
}
