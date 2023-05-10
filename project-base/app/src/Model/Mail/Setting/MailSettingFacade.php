<?php

declare(strict_types=1);

namespace App\Model\Mail\Setting;

use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade as BaseMailSettingFacade;

/**
 * @property \App\Component\Setting\Setting $setting
 * @method __construct(\App\Component\Setting\Setting $setting)
 */
class MailSettingFacade extends BaseMailSettingFacade
{
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
