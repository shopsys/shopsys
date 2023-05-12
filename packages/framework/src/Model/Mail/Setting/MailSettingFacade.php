<?php

namespace Shopsys\FrameworkBundle\Model\Mail\Setting;

use Shopsys\FrameworkBundle\Component\Setting\Setting;

class MailSettingFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(
        protected readonly Setting $setting
    ) {
    }

    /**
     * @param int $domainId
     * @return string
     */
    public function getMainAdminMail($domainId)
    {
        return $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $domainId);
    }

    /**
     * @param int $domainId
     * @return string
     */
    public function getMainAdminMailName($domainId)
    {
        return $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $domainId);
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getMailWhitelist(int $domainId): ?string
    {
        return $this->setting->getForDomain(MailSetting::MAIL_WHITELIST, $domainId);
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isWhitelistEnabled(int $domainId): bool
    {
        return (bool)$this->setting->getForDomain(MailSetting::MAIL_WHITELIST_ENABLED, $domainId);
    }

    /**
     * @param string $mainAdminMail
     * @param int $domainId
     */
    public function setMainAdminMail($mainAdminMail, $domainId)
    {
        $this->setting->setForDomain(MailSetting::MAIN_ADMIN_MAIL, $mainAdminMail, $domainId);
    }

    /**
     * @param string $mainAdminMailName
     * @param int $domainId
     */
    public function setMainAdminMailName($mainAdminMailName, $domainId)
    {
        $this->setting->setForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $mainAdminMailName, $domainId);
    }

    /**
     * @param string|null $value
     * @param int $domainId
     */
    public function setMailWhitelist(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(MailSetting::MAIL_WHITELIST, $value, $domainId);
    }

    /**
     * @param bool $enabled
     * @param int $domainId
     */
    public function setWhitelistEnabled(bool $enabled, int $domainId): void
    {
        $this->setting->setForDomain(MailSetting::MAIL_WHITELIST_ENABLED, $enabled, $domainId);
    }
}
