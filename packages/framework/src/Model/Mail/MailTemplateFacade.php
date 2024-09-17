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
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateRepository $mailTemplateRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFactoryInterface $mailTemplateFactory
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateDataFactoryInterface $mailTemplateDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateAttachmentFilepathProvider $mailTemplateAttachmentFilepathProvider
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateBuilder $mailTemplateBuilder
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly MailTemplateRepository $mailTemplateRepository,
        protected readonly Domain $domain,
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly MailTemplateFactoryInterface $mailTemplateFactory,
        protected readonly MailTemplateDataFactoryInterface $mailTemplateDataFactory,
        protected readonly MailTemplateAttachmentFilepathProvider $mailTemplateAttachmentFilepathProvider,
        protected readonly MailTemplateBuilder $mailTemplateBuilder,
    ) {
    }

    /**
     * @param string $templateName
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplate
     */
    public function get($templateName, $domainId)
    {
        $mailTemplate = $this->mailTemplateRepository->getByNameAndDomainId($templateName, $domainId);

        if ($mailTemplate !== null) {
            $mailTemplate->setBody($this->mailTemplateBuilder->getMailTemplateWithContent($domainId, $mailTemplate->getBody()));
            $this->em->detach($mailTemplate);
        }

        return $mailTemplate;
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplate
     */
    public function getById(int $id): MailTemplate
    {
        return $this->mailTemplateRepository->getById($id);
    }

    /**
     * @param int $id
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateData $mailTemplateData
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplate
     */
    public function edit(int $id, MailTemplateData $mailTemplateData): MailTemplate
    {
        $mailTemplate = $this->getById($id);
        $mailTemplate->edit($mailTemplateData);

        $this->uploadedFileFacade->manageFiles($mailTemplate, $mailTemplateData->attachments);

        $this->em->flush();

        return $mailTemplate;
    }

    /**
     * @param string $name
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus|null $orderStatus
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus|null $complaintStatus
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $attachment
     * @return string
     */
    public function getMailTemplateAttachmentFilepath(UploadedFile $attachment)
    {
        return $this->mailTemplateAttachmentFilepathProvider->getTemporaryFilepath($attachment);
    }

    /**
     * @return bool
     */
    public function existsTemplateWithEnabledSendingHavingEmptyBodyOrSubject()
    {
        return $this->mailTemplateRepository->existsTemplateWithEnabledSendingHavingEmptyBodyOrSubject();
    }
}
