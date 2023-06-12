<?php

declare(strict_types=1);

namespace App\Model\Mail;

use App\Model\Mail\Exception\MailTemplateAlreadyExistsException;
use App\Model\Order\Order;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate as BaseMailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateAttachmentFilepathProvider;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade as BaseMailTemplateFacade;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateRepository;

/**
 * @property \App\Model\Mail\MailTemplateRepository $mailTemplateRepository
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @property \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
 * @method \App\Model\Mail\MailTemplate getById(int $id)
 * @method \App\Model\Mail\MailTemplate edit(int $id, \App\Model\Mail\MailTemplateData $mailTemplateData)
 */
class MailTemplateFacade extends BaseMailTemplateFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Mail\MailTemplateRepository $mailTemplateRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFactoryInterface $mailTemplateFactory
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateDataFactoryInterface $mailTemplateDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateAttachmentFilepathProvider $mailTemplateAttachmentFilepathProvider
     * @param \App\Model\Mail\MailTemplateBuilder $mailTemplateBuilder
     */
    public function __construct(
        EntityManagerInterface $em,
        MailTemplateRepository $mailTemplateRepository,
        Domain $domain,
        UploadedFileFacade $uploadedFileFacade,
        MailTemplateFactoryInterface $mailTemplateFactory,
        MailTemplateDataFactoryInterface $mailTemplateDataFactory,
        MailTemplateAttachmentFilepathProvider $mailTemplateAttachmentFilepathProvider,
        private readonly MailTemplateBuilder $mailTemplateBuilder,
    ) {
        parent::__construct($em, $mailTemplateRepository, $domain, $uploadedFileFacade, $mailTemplateFactory, $mailTemplateDataFactory, $mailTemplateAttachmentFilepathProvider);
    }

    /**
     * @param \App\Model\Mail\MailTemplateData $mailTemplateData
     * @return \App\Model\Mail\MailTemplate
     */
    public function createOrderStatusTemplate(MailTemplateData $mailTemplateData): MailTemplate
    {
        $existingMailTemplate = $this->mailTemplateRepository->findOrderStatusMailTemplate(
            $mailTemplateData->domainId,
            $mailTemplateData->orderStatus,
        );

        if ($existingMailTemplate !== null) {
            throw new MailTemplateAlreadyExistsException($existingMailTemplate);
        }

        /** @var \App\Model\Mail\MailTemplate $mailTemplate */
        $mailTemplate = $this->mailTemplateFactory->create(
            MailTemplate::ORDER_STATUS_NAME,
            $mailTemplateData->domainId,
            $mailTemplateData,
        );

        $this->em->persist($mailTemplate);
        $this->em->flush();

        return $mailTemplate;
    }

    /**
     * @param \App\Model\Mail\MailTemplate $mailTemplate
     */
    public function delete(MailTemplate $mailTemplate): void
    {
        if ($mailTemplate->getName() !== MailTemplate::ORDER_STATUS_NAME) {
            throw new Exception\DeleteMailTemplateException();
        }

        $this->em->remove($mailTemplate);
        $this->em->flush();
    }

    /**
     * @param \App\Model\Order\Order $order
     * @return \App\Model\Mail\MailTemplate[]
     */
    public function getOrderStatusTemplatesByOrder(Order $order): array
    {
        $mailTemplates = [];
        $mailTemplate = $this->mailTemplateRepository->findOrderStatusMailTemplate(
            $order->getDomainId(),
            $order->getStatus(),
        );

        if ($mailTemplate !== null) {
            $mailTemplate->setBody($this->mailTemplateBuilder->getMailTemplateWithContent($order->getDomainId(), $mailTemplate->getBody()));
            $this->em->detach($mailTemplate);

            $mailTemplates[] = $mailTemplate;
        }

        return $mailTemplates;
    }

    /**
     * @param string $templateName
     * @param int $domainId
     * @return \App\Model\Mail\MailTemplate
     */
    public function get($templateName, $domainId): BaseMailTemplate
    {
        $mailTemplate = $this->mailTemplateRepository->getByNameAndDomainId($templateName, $domainId);
        if ($mailTemplate !== null) {
            $mailTemplate->setBody($this->mailTemplateBuilder->getMailTemplateWithContent($domainId, $mailTemplate->getBody()));
            $this->em->detach($mailTemplate);
        }

        return $mailTemplate;
    }
}
