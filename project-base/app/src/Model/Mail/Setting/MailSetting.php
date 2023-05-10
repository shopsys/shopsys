<?php

declare(strict_types=1);

namespace App\Model\Mail\Setting;

use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting as BaseMailSetting;

class MailSetting extends BaseMailSetting
{
    public const MAIL_FACEBOOK_URL = 'mailFacebookUrl';
    public const MAIL_INSTAGRAM_URL = 'mailInstagramUrl';
    public const MAIL_YOUTUBE_URL = 'mailYoutubeUrl';
    public const MAIL_LINKEDIN_URL = 'mailLinkedinUrl';
    public const MAIL_TIKTOK_URL = 'mailTiktokUrl';
    public const MAIL_FOOTER_TEXT = 'mailFooterText';
}
