<?php

declare(strict_types=1);

namespace App\Model\Mail;

use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade as BaseMailTemplateFacade;

/**
 * @property \App\Model\Mail\MailTemplateRepository $mailTemplateRepository
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @property \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
 * @method \App\Model\Mail\MailTemplate getById(int $id)
 * @method \App\Model\Mail\MailTemplate edit(int $id, \App\Model\Mail\MailTemplateData $mailTemplateData)
 * @property \App\Model\Mail\MailTemplateDataFactory $mailTemplateDataFactory
 * @property \App\Model\Mail\MailTemplateBuilder $mailTemplateBuilder
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Mail\MailTemplateRepository $mailTemplateRepository, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade, \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFactoryInterface $mailTemplateFactory, \App\Model\Mail\MailTemplateDataFactory $mailTemplateDataFactory, \Shopsys\FrameworkBundle\Model\Mail\MailTemplateAttachmentFilepathProvider $mailTemplateAttachmentFilepathProvider, \App\Model\Mail\MailTemplateBuilder $mailTemplateBuilder)
 * @method \App\Model\Mail\MailTemplate get(string $templateName, int $domainId)
 * @method createMailTemplateForAllDomains(string $name, \App\Model\Order\Status\OrderStatus|null $orderStatus = null, \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus|null $complaintStatus = null)
 */
class MailTemplateFacade extends BaseMailTemplateFacade
{
}
