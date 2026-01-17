<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Mail\MailerSettingProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MailerSettingExtension extends AbstractExtension
{
    public function __construct(
        protected readonly MailerSettingProvider $mailerSettingProvider,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('isMailerSettingUnusual', $this->isMailerSettingUnusual(...)),
            new TwigFunction('getMailerSettingInfo', $this->getMailerSettingInfo(...), ['is_safe' => ['html']]),
        ];
    }

    public function isMailerSettingUnusual(): bool
    {
        if ($this->mailerSettingProvider->isDeliveryDisabled()) {
            return true;
        }

        foreach ($this->domain->getAllIds() as $domainId) {
            if ($this->mailerSettingProvider->isWhitelistEnabled($domainId)) {
                return true;
            }
        }

        return false;
    }

    public function getMailerSettingInfo(): string
    {
        if ($this->mailerSettingProvider->isDeliveryDisabled()) {
            return t('Sending emails is turned off, no email will be sent from the app.');
        }

        foreach ($this->domain->getAllIds() as $domainId) {
            if ($this->mailerSettingProvider->isWhitelistEnabled($domainId)) {
                return t('Email whitelist is enabled on some domains. Only emails matching the whitelist will be sent.');
            }
        }

        return t('No email whitelist is enabled, all emails will be sent.');
    }

    public function getName(): string
    {
        return 'shopsys.twig.mailer_setting_extension';
    }
}
