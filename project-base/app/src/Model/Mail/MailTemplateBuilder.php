<?php

declare(strict_types=1);

namespace App\Model\Mail;

use Shopsys\FrameworkBundle\Model\Mail\MailTemplateBuilder as BaseMailTemplateBuilder;

/**
 * @property \App\Model\Mail\Setting\MailSettingFacade $mailSettingFacade
 * @method __construct(\App\Model\Mail\Setting\MailSettingFacade $mailSettingFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Component\Cdn\CdnFacade $cdnFacade)
 */
class MailTemplateBuilder extends BaseMailTemplateBuilder
{
}
