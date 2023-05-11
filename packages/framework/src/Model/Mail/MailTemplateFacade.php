<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;

class MailTemplateFacade
{
    protected EntityManagerInterface $em;

    protected MailTemplateRepository $mailTemplateRepository;

    protected Domain $domain;

    protected UploadedFileFacade $uploadedFileFacade;

    protected MailTemplateFactoryInterface $mailTemplateFactory;

    protected MailTemplateDataFactoryInterface $mailTemplateDataFactory;

    protected MailTemplateAttachmentFilepathProvider $mailTemplateAttachmentFilepathProvider;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateRepository $mailTemplateRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFactoryInterface $mailTemplateFactory
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateDataFactoryInterface $mailTemplateDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateAttachmentFilepathProvider $mailTemplateAttachmentFilepathProvider
     */
    public function __construct(
        EntityManagerInterface $em,
        MailTemplateRepository $mailTemplateRepository,
        Domain $domain,
        UploadedFileFacade $uploadedFileFacade,
        MailTemplateFactoryInterface $mailTemplateFactory,
        MailTemplateDataFactoryInterface $mailTemplateDataFactory,
        MailTemplateAttachmentFilepathProvider $mailTemplateAttachmentFilepathProvider
    ) {
        $this->em = $em;
        $this->mailTemplateRepository = $mailTemplateRepository;
        $this->domain = $domain;
        $this->uploadedFileFacade = $uploadedFileFacade;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->mailTemplateDataFactory = $mailTemplateDataFactory;
        $this->mailTemplateAttachmentFilepathProvider = $mailTemplateAttachmentFilepathProvider;
    }

    /**
     * @param string $templateName
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplate
     */
    public function get($templateName, $domainId)
    {
        return $this->mailTemplateRepository->getByNameAndDomainId($templateName, $domainId);
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
     */
    public function createMailTemplateForAllDomains($name)
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $mailTemplateData = $this->mailTemplateDataFactory->create();
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
