<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use Nette\Utils\Json;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade;

class MailerSettingProvider
{
    protected bool $deliveryDisabled;

    /**
     * @param string $mailerDsn
     * @param bool $whitelistForced
     * @param \Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade $mailSettingFacade
     */
    public function __construct(
        string $mailerDsn,
        protected readonly bool $whitelistForced,
        protected readonly MailSettingFacade $mailSettingFacade,
    ) {
        $this->deliveryDisabled = $mailerDsn === Mailer::DISABLED_MAILER_DSN;
    }

    /**
     * @param int $domainId
     * @return string[]
     */
    public function getWhitelistPatternsAsArray(int $domainId): array
    {
        $mailWhitelist = $this->mailSettingFacade->getMailWhitelist($domainId);

        return $mailWhitelist !== null ? Json::decode($mailWhitelist, Json::FORCE_ARRAY) : [];
    }

    /**
     * @return bool
     */
    public function isWhitelistForced(): bool
    {
        return $this->whitelistForced ?? false;
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isWhitelistEnabled(int $domainId): bool
    {
        return $this->isWhitelistForced() || $this->mailSettingFacade->isWhitelistEnabled($domainId);
    }

    /**
     * @return bool
     */
    public function isDeliveryDisabled(): bool
    {
        return $this->deliveryDisabled;
    }
}
