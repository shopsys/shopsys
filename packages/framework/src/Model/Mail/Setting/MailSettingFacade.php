<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\Setting;

use Shopsys\FrameworkBundle\Component\Setting\Setting;

class MailSettingFacade
{
    public function __construct(
        protected readonly Setting $setting,
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

    public function getMailWhitelist(int $domainId): ?string
    {
        return $this->setting->getForDomain(MailSetting::MAIL_WHITELIST, $domainId);
    }

    public function isWhitelistEnabled(int $domainId): bool
    {
        return (bool)$this->setting->getForDomain(MailSetting::MAIL_WHITELIST_ENABLED, $domainId);
    }

    /**
     * @param string $mainAdminMail
     * @param int $domainId
     */
    public function setMainAdminMail($mainAdminMail, $domainId): void
    {
        $this->setting->setForDomain(MailSetting::MAIN_ADMIN_MAIL, $mainAdminMail, $domainId);
    }

    /**
     * @param string $mainAdminMailName
     * @param int $domainId
     */
    public function setMainAdminMailName($mainAdminMailName, $domainId): void
    {
        $this->setting->setForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $mainAdminMailName, $domainId);
    }

    public function setMailWhitelist(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(MailSetting::MAIL_WHITELIST, $value, $domainId);
    }

    public function setWhitelistEnabled(bool $enabled, int $domainId): void
    {
        $this->setting->setForDomain(MailSetting::MAIL_WHITELIST_ENABLED, $enabled, $domainId);
    }

    public function getFacebookUrl(int $domainId): ?string
    {
        return $this->setting->getForDomain(MailSetting::MAIL_FACEBOOK_URL, $domainId);
    }

    public function getInstagramUrl(int $domainId): ?string
    {
        return $this->setting->getForDomain(MailSetting::MAIL_INSTAGRAM_URL, $domainId);
    }

    public function getYoutubeUrl(int $domainId): ?string
    {
        return $this->setting->getForDomain(MailSetting::MAIL_YOUTUBE_URL, $domainId);
    }

    public function getLinkedInUrl(int $domainId): ?string
    {
        return $this->setting->getForDomain(MailSetting::MAIL_LINKEDIN_URL, $domainId);
    }

    public function getTiktokUrl(int $domainId): ?string
    {
        return $this->setting->getForDomain(MailSetting::MAIL_TIKTOK_URL, $domainId);
    }

    public function getFooterText(int $domainId): ?string
    {
        return $this->setting->getForDomain(MailSetting::MAIL_FOOTER_TEXT, $domainId);
    }

    /**
     * @return array<string, string|null>
     */
    public function getFooterIconUrls(int $domainId): array
    {
        return [
            'facebook' => $this->getFacebookUrl($domainId),
            'instagram' => $this->getInstagramUrl($domainId),
            'youtube' => $this->getYoutubeUrl($domainId),
            'linkedin' => $this->getLinkedInUrl($domainId),
            'tiktok' => $this->getTiktokUrl($domainId),
        ];
    }

    public function setFacebookUrl(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(MailSetting::MAIL_FACEBOOK_URL, $value, $domainId);
    }

    public function setInstagramUrl(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(MailSetting::MAIL_INSTAGRAM_URL, $value, $domainId);
    }

    public function setYoutubeUrl(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(MailSetting::MAIL_YOUTUBE_URL, $value, $domainId);
    }

    public function setLinkedInUrl(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(MailSetting::MAIL_LINKEDIN_URL, $value, $domainId);
    }

    public function setTiktokUrl(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(MailSetting::MAIL_TIKTOK_URL, $value, $domainId);
    }

    /**
     * @throws \Shopsys\FrameworkBundle\Component\Setting\Exception\SettingValueNotFoundException
     */
    public function setFooterText(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(MailSetting::MAIL_FOOTER_TEXT, $value, $domainId);
    }
}
