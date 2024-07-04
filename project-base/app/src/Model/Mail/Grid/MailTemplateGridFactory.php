<?php

declare(strict_types=1);

namespace App\Model\Mail\Grid;

use Shopsys\FrameworkBundle\Model\Mail\Grid\MailTemplateGridFactory as BaseMailTemplateGridFactory;

/**
 * @property \App\Model\Mail\MailTemplateRepository $mailTemplateRepository
 * @method __construct(\App\Model\Mail\MailTemplateRepository $mailTemplateRepository, \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory, \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade, \App\Model\Mail\MailTemplateConfiguration $mailTemplateConfiguration)
 * @property \App\Model\Mail\MailTemplateConfiguration $mailTemplateConfiguration
 */
class MailTemplateGridFactory extends BaseMailTemplateGridFactory
{
}
