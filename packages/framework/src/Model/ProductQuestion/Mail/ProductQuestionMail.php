<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductQuestion\Mail;

use Shopsys\FrameworkBundle\Component\Mailer\MailerHelper;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade;
use Shopsys\FrameworkBundle\Model\ProductQuestion\ProductQuestionData;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductQuestionMail
{
    public const string CUSTOMER_MAIL_TEMPLATE_NAME = 'product_question_customer';
    public const string ADMIN_MAIL_TEMPLATE_NAME = 'product_question_admin';

    public const string VARIABLE_CUSTOMER_NAME = '{customerName}';
    public const string VARIABLE_EMAIL = '{email}';
    public const string VARIABLE_QUESTION = '{question}';
    public const string VARIABLE_PRODUCT_NAME = '{productName}';
    public const string VARIABLE_PRODUCT_URL = '{productUrl}';

    public function __construct(
        protected readonly MailSettingFacade $mailSettingFacade,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly MailerHelper $mailerHelper,
    ) {
    }

    public function createMessageForAdmin(
        MailTemplate $template,
        ProductQuestionData $productQuestionData,
    ): MessageData {
        $mainAdminMail = $this->mailSettingFacade->getMainAdminMail($productQuestionData->domainId);

        return new MessageData(
            $mainAdminMail,
            $template->getBccEmail(),
            $template->getBody(),
            $template->getSubject(),
            $mainAdminMail,
            $this->mailSettingFacade->getMainAdminMailName($productQuestionData->domainId),
            $this->getBodyVariablesReplacements($productQuestionData),
            $this->getSubjectVariablesReplacements($productQuestionData),
            replyTo: $productQuestionData->email,
        );
    }

    public function createMessageForCustomer(
        MailTemplate $template,
        ProductQuestionData $productQuestionData,
    ): MessageData {
        return new MessageData(
            $productQuestionData->email,
            $template->getBccEmail(),
            $template->getBody(),
            $template->getSubject(),
            $this->mailSettingFacade->getMainAdminMail($productQuestionData->domainId),
            $this->mailSettingFacade->getMainAdminMailName($productQuestionData->domainId),
            $this->getBodyVariablesReplacements($productQuestionData),
            $this->getSubjectVariablesReplacements($productQuestionData),
        );
    }

    /**
     * @return array<string, \Closure>
     */
    protected function getSubjectVariablesReplacements(ProductQuestionData $productQuestionData): array
    {
        return [
            self::VARIABLE_CUSTOMER_NAME => fn () => $this->mailerHelper->escapeString($productQuestionData->customerName),
            self::VARIABLE_EMAIL => fn () => $this->mailerHelper->escapeString($productQuestionData->email),
            self::VARIABLE_PRODUCT_NAME => fn () => $productQuestionData->product->getName(),
        ];
    }

    /**
     * @return array<string, \Closure>
     */
    protected function getBodyVariablesReplacements(ProductQuestionData $productQuestionData): array
    {
        return [
            ...$this->getSubjectVariablesReplacements($productQuestionData),
            self::VARIABLE_QUESTION => fn () => $this->mailerHelper->escapeString($productQuestionData->question),
            self::VARIABLE_PRODUCT_URL => fn () => $this->getProductUrl($productQuestionData),
        ];
    }

    protected function getProductUrl(ProductQuestionData $productQuestionData): string
    {
        return $this->domainRouterFactory->getRouter($productQuestionData->domainId)->generate(
            'front_product_detail',
            ['id' => $productQuestionData->product->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
