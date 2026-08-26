<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\GrapesJs\EnsureCorrectGrapesJsFormatHelper;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactory;

class MailTemplateDataFactory
{
    public function __construct(
        protected readonly UploadedFileDataFactory $uploadedFileDataFactory,
        protected readonly Domain $domain,
        protected readonly EnsureCorrectGrapesJsFormatHelper $ensureCorrectGrapesJsFormatHelper,
    ) {
    }

    protected function createInstance(): MailTemplateData
    {
        return new MailTemplateData();
    }

    public function create(): MailTemplateData
    {
        $mailTemplateData = $this->createInstance();
        $mailTemplateData->attachments = $this->uploadedFileDataFactory->create();

        return $mailTemplateData;
    }

    public function createFromMailTemplate(MailTemplate $mailTemplate): MailTemplateData
    {
        $mailTemplateData = $this->createInstance();
        $this->fillFromMailTemplate($mailTemplateData, $mailTemplate);
        $mailTemplateData->attachments = $this->uploadedFileDataFactory->createByEntity($mailTemplate);

        return $mailTemplateData;
    }

    protected function fillFromMailTemplate(MailTemplateData $mailTemplateData, MailTemplate $mailTemplate): void
    {
        $mailTemplateData->name = $mailTemplate->getName();
        $mailTemplateData->bccEmail = $mailTemplate->getBccEmail();
        $mailTemplateData->subject = $mailTemplate->getSubject();
        $mailTemplateData->body = $this->ensureCorrectGrapesJsFormatHelper->ensureStringIsInCorrectGrapesJsFormat(
            $mailTemplate->getBody(),
            $this->domain->getDomainConfigById($mailTemplate->getDomainId())->getLocale(),
        );
        $mailTemplateData->sendMail = $mailTemplate->isSendMail();
        $mailTemplateData->orderStatus = $mailTemplate->getOrderStatus();
        $mailTemplateData->complaintStatus = $mailTemplate->getComplaintStatus();
        $mailTemplateData->domainId = $mailTemplate->getDomainId();
    }
}
