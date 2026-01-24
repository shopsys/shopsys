<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;

class MailTemplateFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly MailTemplateRepository $mailTemplateRepository,
        protected readonly Domain $domain,
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly MailTemplateFactory $mailTemplateFactory,
        protected readonly MailTemplateDataFactory $mailTemplateDataFactory,
        protected readonly MailTemplateAttachmentFilepathProvider $mailTemplateAttachmentFilepathProvider,
        protected readonly MailTemplateBuilder $mailTemplateBuilder,
    ) {
    }

    public function getWrappedWithGrapesJsBody(string $templateName, int $domainId): MailTemplate
    {
        $mailTemplate = $this->mailTemplateRepository->getByNameAndDomainId($templateName, $domainId);

        return $this->getTemplateWrappedWithGrapesBody($mailTemplate);
    }

    public function getById(int $id): MailTemplate
    {
        return $this->mailTemplateRepository->getById($id);
    }

    public function edit(int $id, MailTemplateData $mailTemplateData): MailTemplate
    {
        $mailTemplate = $this->getById($id);
        $mailTemplate->edit($mailTemplateData);

        $this->uploadedFileFacade->manageFiles($mailTemplate, $mailTemplateData->attachments);

        $this->em->flush();

        return $mailTemplate;
    }

    public function createMailTemplateForAllDomains(
        string $name,
        ?OrderStatus $orderStatus = null,
        ?ComplaintStatus $complaintStatus = null,
    ): void {
        foreach ($this->domain->getAll() as $domainConfig) {
            $mailTemplateData = $this->mailTemplateDataFactory->create();
            $mailTemplateData->orderStatus = $orderStatus;
            $mailTemplateData->complaintStatus = $complaintStatus;
            $mailTemplate = $this->mailTemplateFactory->create($name, $domainConfig->getId(), $mailTemplateData);
            $this->em->persist($mailTemplate);
        }

        $this->em->flush();
    }

    public function getMailTemplateAttachmentFilepath(UploadedFile $attachment): string
    {
        return $this->mailTemplateAttachmentFilepathProvider->getTemporaryFilepath($attachment);
    }

    public function existsTemplateWithEnabledSendingHavingEmptyBodyOrSubject(): bool
    {
        return $this->mailTemplateRepository->existsTemplateWithEnabledSendingHavingEmptyBodyOrSubject();
    }

    public function getTemplateWrappedWithGrapesBody(MailTemplate $mailTemplate): MailTemplate
    {
        $mailTemplate->setBody($this->mailTemplateBuilder->getMailTemplateWithContent($mailTemplate->getDomainId(), $mailTemplate->getBody()));
        $this->em->detach($mailTemplate); // detach from entity manager to avoid accidental persisting the changes to the database

        return $mailTemplate;
    }
}
