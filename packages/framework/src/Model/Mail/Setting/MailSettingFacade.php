<?php

namespace Shopsys\FrameworkBundle\Model\Mail\Setting;

use Shopsys\FrameworkBundle\Component\Setting\Setting;

class MailSettingFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    protected $setting;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(
        Setting $setting
    ) {
        $this->setting = $setting;
    }

    /**
     * @param int $domainId
     * @return string
     */
    public function getMainAdminMail(int $domainId): string
    {
        return $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $domainId);
    }

    /**
     * @param int $domainId
     * @return string
     */
    public function getMainAdminMailName(int $domainId): string
    {
        return $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $domainId);
    }

    /**
     * @param string $mainAdminMail
     * @param int $domainId
     */
    public function setMainAdminMail(string $mainAdminMail, int $domainId): void
    {
        $this->setting->setForDomain(MailSetting::MAIN_ADMIN_MAIL, $mainAdminMail, $domainId);
    }

    /**
     * @param string $mainAdminMailName
     * @param int $domainId
     */
    public function setMainAdminMailName(string $mainAdminMailName, int $domainId): void
    {
        $this->setting->setForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $mainAdminMailName, $domainId);
    }
}
