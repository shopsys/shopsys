<?php

declare(strict_types=1);

namespace App\Model\Mail;

use App\Model\Mail\Exception\MailTemplateAlreadyExistsException;
use App\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade as BaseMailTemplateFacade;

/**
 * @property \App\Model\Mail\MailTemplateRepository $mailTemplateRepository
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @property \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Mail\MailTemplateRepository $mailTemplateRepository, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade, \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFactoryInterface $mailTemplateFactory, \Shopsys\FrameworkBundle\Model\Mail\MailTemplateDataFactoryInterface $mailTemplateDataFactory, \Shopsys\FrameworkBundle\Model\Mail\MailTemplateAttachmentFilepathProvider $mailTemplateAttachmentFilepathProvider)
 * @method \App\Model\Mail\MailTemplate get(string $templateName, int $domainId)
 * @method \App\Model\Mail\MailTemplate getById(int $id)
 * @method \App\Model\Mail\MailTemplate edit(int $id, \App\Model\Mail\MailTemplateData $mailTemplateData)
 */
class MailTemplateFacade extends BaseMailTemplateFacade
{
    /**
     * @param \App\Model\Mail\MailTemplateData $mailTemplateData
     * @return \App\Model\Mail\MailTemplate
     */
    public function createOrderStatusTemplate(MailTemplateData $mailTemplateData): MailTemplate
    {
        $existingMailTemplate = $this->mailTemplateRepository->findOrderStatusMailTemplate(
            $mailTemplateData->domainId,
            $mailTemplateData->transport,
            $mailTemplateData->payment,
            $mailTemplateData->orderStatus,
        );

        if ($existingMailTemplate !== null) {
            throw new MailTemplateAlreadyExistsException($existingMailTemplate);
        }

        /** @var \App\Model\Mail\MailTemplate $mailTemplate */
        $mailTemplate = $this->mailTemplateFactory->create(
            MailTemplate::ORDER_STATUS_NAME,
            $mailTemplateData->domainId,
            $mailTemplateData
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
            $order->getTransport(),
            $order->getPayment(),
            $order->getStatus()
        );
        if ($mailTemplate !== null) {
            $mailTemplates[] = $mailTemplate;
        }

        return $mailTemplates;
    }
}
