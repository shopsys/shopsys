<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\Setting;

use Shopsys\FrameworkBundle\Component\Setting\Setting;

class MailSettingFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
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

    /**
     * @param int $domainId
     * @return string
     */
    public function getFacebookUrl(int $domainId): string
    {
        return $this->setting->getForDomain(MailSetting::MAIL_FACEBOOK_URL, $domainId) ?? '';
    }

    /**
     * @param int $domainId
     * @return string
     */
    public function getInstagramUrl(int $domainId): string
    {
        return $this->setting->getForDomain(MailSetting::MAIL_INSTAGRAM_URL, $domainId) ?? '';
    }

    /**
     * @param int $domainId
     * @return string
     */
    public function getYoutubeUrl(int $domainId): string
    {
        return $this->setting->getForDomain(MailSetting::MAIL_YOUTUBE_URL, $domainId) ?? '';
    }

    /**
     * @param int $domainId
     * @return string
     */
    public function getLinkedInUrl(int $domainId): string
    {
        return $this->setting->getForDomain(MailSetting::MAIL_LINKEDIN_URL, $domainId) ?? '';
    }

    /**
     * @param int $domainId
     * @return string
     */
    public function getTiktokUrl(int $domainId): string
    {
        return $this->setting->getForDomain(MailSetting::MAIL_TIKTOK_URL, $domainId) ?? '';
    }

    /**
     * @param int $domainId
     * @return string
     */
    public function getFooterTextUrl(int $domainId): string
    {
        return $this->setting->getForDomain(MailSetting::MAIL_FOOTER_TEXT, $domainId);
    }

    /**
     * @param int $domainId
     * @return array<string, string|null>
     */
    public function getFooterIconUrls(int $domainId): array
    {
        return [
            'facebook' => strlen($this->getFacebookUrl($domainId)) === 0 ? null : $this->getFacebookUrl($domainId),
            'instagram' => strlen($this->getInstagramUrl($domainId)) === 0 ? null : $this->getInstagramUrl($domainId),
            'youtube' => strlen($this->getYoutubeUrl($domainId)) === 0 ? null : $this->getYoutubeUrl($domainId),
            'linkedin' => strlen($this->getLinkedInUrl($domainId)) === 0 ? null : $this->getLinkedInUrl($domainId),
            'tiktok' => strlen($this->getTiktokUrl($domainId)) === 0 ? null : $this->getTiktokUrl($domainId),
        ];
    }

    /**
     * @param string|null $value
     * @param int $domainId
     */
    public function setFacebookUrl(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(MailSetting::MAIL_FACEBOOK_URL, $value, $domainId);
    }

    /**
     * @param string|null $value
     * @param int $domainId
     */
    public function setInstagramUrl(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(MailSetting::MAIL_INSTAGRAM_URL, $value, $domainId);
    }

    /**
     * @param string|null $value
     * @param int $domainId
     */
    public function setYoutubeUrl(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(MailSetting::MAIL_YOUTUBE_URL, $value, $domainId);
    }

    /**
     * @param string|null $value
     * @param int $domainId
     */
    public function setLinkedInUrl(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(MailSetting::MAIL_LINKEDIN_URL, $value, $domainId);
    }

    /**
     * @param string|null $value
     * @param int $domainId
     */
    public function setTiktokUrl(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(MailSetting::MAIL_TIKTOK_URL, $value, $domainId);
    }

    /**
     * @param string $value
     * @param int $domainId
     */
    public function setFooterText(string $value, int $domainId): void
    {
        $this->setting->setForDomain(MailSetting::MAIL_FOOTER_TEXT, $value, $domainId);
    }
}
