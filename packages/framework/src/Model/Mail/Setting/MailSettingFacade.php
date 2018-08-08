<?php

namespace Shopsys\FrameworkBundle\Model\Mail\Setting;

use Shopsys\FrameworkBundle\Component\Setting\Setting;

class MailSettingFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    protected $setting;

    public function __construct(
        Setting $setting
    ) {
        $this->setting = $setting;
    }
    
    public function getMainAdminMail(int $domainId): string
    {
        return $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $domainId);
    }
    
    public function getMainAdminMailName(int $domainId): string
    {
        return $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $domainId);
    }
    
    public function setMainAdminMail(string $mainAdminMail, int $domainId): void
    {
        $this->setting->setForDomain(MailSetting::MAIN_ADMIN_MAIL, $mainAdminMail, $domainId);
    }
    
    public function setMainAdminMailName(string $mainAdminMailName, int $domainId): void
    {
        $this->setting->setForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $mainAdminMailName, $domainId);
    }
}
