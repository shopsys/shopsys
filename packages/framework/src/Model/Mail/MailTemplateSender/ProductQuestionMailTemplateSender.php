<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender;

use Override;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\ProductQuestion\Mail\ProductQuestionMail;
use Shopsys\FrameworkBundle\Model\ProductQuestion\Mail\ProductQuestionMailFacade;
use Shopsys\FrameworkBundle\Model\ProductQuestion\ProductQuestionDataFactory;

class ProductQuestionMailTemplateSender implements MailTemplateSenderInterface
{
    public function __construct(
        protected readonly ProductQuestionMailFacade $productQuestionMailFacade,
        protected readonly ProductQuestionDataFactory $productQuestionDataFactory,
        protected readonly ProductFacade $productFacade,
    ) {
    }

    #[Override]
    public function getFormLabelForEntityIdentifier(): ?string
    {
        return t('Product ID');
    }

    #[Override]
    public function supports(MailTemplate $mailTemplate): bool
    {
        return in_array($mailTemplate->getName(), [
            ProductQuestionMail::CUSTOMER_MAIL_TEMPLATE_NAME,
            ProductQuestionMail::ADMIN_MAIL_TEMPLATE_NAME,
        ], true);
    }

    #[Override]
    public function sendTemplate(MailTemplate $mailTemplate, string $mailTo, ?int $entityId): void
    {
        $productQuestionData = $this->productQuestionDataFactory->create($mailTemplate->getDomainId());
        $productQuestionData->customerName = t('Sample customer name');
        $productQuestionData->email = $mailTo;
        $productQuestionData->question = t('Sample product question.');
        $productQuestionData->product = $this->productFacade->getById($entityId);

        $this->productQuestionMailFacade->sendMailTemplate($mailTemplate, $productQuestionData, $mailTo);
    }
}
