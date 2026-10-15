<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentFactory;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactory;

class MailTemplateDataFactory
{
    public function __construct(
        protected readonly UploadedFileDataFactory $uploadedFileDataFactory,
        protected readonly ContentDocumentFactory $contentDocumentFactory,
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
        $mailTemplateData->content = $this->contentDocumentFactory->createFromArray(
            $mailTemplate->getContentArray() ?? ['profile' => ContentProfileEnum::MAIL],
        );
        $mailTemplateData->sendMail = $mailTemplate->isSendMail();
        $mailTemplateData->orderStatus = $mailTemplate->getOrderStatus();
        $mailTemplateData->complaintStatus = $mailTemplate->getComplaintStatus();
        $mailTemplateData->domainId = $mailTemplate->getDomainId();
    }
}
