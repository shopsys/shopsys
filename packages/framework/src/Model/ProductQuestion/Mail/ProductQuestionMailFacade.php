<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductQuestion\Mail;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Mail\Exception\MailTemplateNotFoundException;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;
use Shopsys\FrameworkBundle\Model\ProductQuestion\ProductQuestionData;

class ProductQuestionMailFacade
{
    public function __construct(
        protected readonly Mailer $mailer,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly ProductQuestionMail $productQuestionMail,
        protected readonly UploadedFileFacade $uploadedFileFacade,
    ) {
    }

    public function sendMail(ProductQuestionData $productQuestionData): void
    {
        $adminMailTemplate = $this->mailTemplateFacade->getWrappedWithGrapesJsBody(ProductQuestionMail::ADMIN_MAIL_TEMPLATE_NAME, $productQuestionData->domainId);
        $this->sendMailTemplate($adminMailTemplate, $productQuestionData);

        $customerMailTemplate = $this->mailTemplateFacade->getWrappedWithGrapesJsBody(ProductQuestionMail::CUSTOMER_MAIL_TEMPLATE_NAME, $productQuestionData->domainId);
        $this->sendMailTemplate($customerMailTemplate, $productQuestionData);
    }

    public function sendMailTemplate(
        MailTemplate $mailTemplate,
        ProductQuestionData $productQuestionData,
        ?string $forceSendTo = null,
    ): void {
        $messageData = match ($mailTemplate->getName()) {
            ProductQuestionMail::ADMIN_MAIL_TEMPLATE_NAME => $this->productQuestionMail->createMessageForAdmin($mailTemplate, $productQuestionData),
            ProductQuestionMail::CUSTOMER_MAIL_TEMPLATE_NAME => $this->productQuestionMail->createMessageForCustomer($mailTemplate, $productQuestionData),
            default => throw new MailTemplateNotFoundException($mailTemplate->getName()),
        };

        if ($forceSendTo !== null) {
            $messageData->toEmail = $forceSendTo;
        }

        $messageData->attachments = $this->uploadedFileFacade->getUploadedFilesByEntity($mailTemplate);
        $this->mailer->sendForDomain($messageData, $mailTemplate->getDomainId());
    }
}
