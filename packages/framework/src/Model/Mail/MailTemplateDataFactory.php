<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\GrapesJs\EnsureCorrectGrapesJsFormatHelper;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMail;

class MailTemplateDataFactory
{
    public function __construct(
        protected readonly UploadedFileDataFactory $uploadedFileDataFactory,
        protected readonly Domain $domain,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
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

    protected function fillFromMailTemplate(MailTemplateData $mailTemplateData, MailTemplate $mailTemplate)
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus[] $orderStatuses
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate[] $mailTemplates
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplateData[]
     */
    public function createFromOrderStatuses(array $orderStatuses, array $mailTemplates): array
    {
        $orderStatusMailTemplatesData = [];

        foreach ($orderStatuses as $orderStatus) {
            $mailTemplate = OrderMail::findMailTemplateForOrderStatus($mailTemplates, $orderStatus);

            if ($mailTemplate !== null) {
                $orderStatusMailTemplateData = $this->createFromMailTemplate($mailTemplate);
            } else {
                $orderStatusMailTemplateData = $this->create();
            }
            $orderStatusMailTemplateData->name = OrderMail::getMailTemplateNameByStatus($orderStatus);

            $orderStatusMailTemplatesData[$orderStatus->getId()] = $orderStatusMailTemplateData;
        }

        return $orderStatusMailTemplatesData;
    }
}
