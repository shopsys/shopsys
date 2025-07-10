<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Customer\Mail\CustomerActivationMail;
use App\Model\Mail\MailTemplate;
use App\Model\Mail\MailTemplateData;
use App\Model\Mail\MailTemplateDataFactory;
use App\Model\Order\Status\OrderStatus;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Administrator\Mail\ResetPasswordMail;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFactory;

class MailTemplateDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFactory $mailTemplateFactory
     * @param \App\Model\Mail\MailTemplateDataFactory $mailTemplateDataFactory
     */
    public function __construct(
        private readonly MailTemplateFactory $mailTemplateFactory,
        private readonly MailTemplateDataFactory $mailTemplateDataFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    #[Override]
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();

            $this->createOrderPlacedMailTemplate($locale, $manager, $domainId);
            $this->createOrderProcessedMailTemplate($locale, $manager, $domainId);
            $this->createOrderFinishedMailTemplate($locale, $manager, $domainId);
            $this->createOrderCanceledMailTemplate($locale, $manager, $domainId);

            $this->createResetPasswordMailTemplate($locale, $manager, $domainId);
            $this->createRegistrationCompleteMailTemplate($locale, $manager, $domainId);

            $this->createPersonalInformationOverviewMailTemplate($locale, $manager, $domainId);
            $this->createPersonalInformationExportMailTemplate($locale, $manager, $domainId);

            $this->createCustomerActivationMailTemplate($locale, $manager, $domainId);

            $this->createAdministratorResetPasswordMailTemplate($locale, $manager, $domainId);
        }
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     * @param string $name
     * @param \App\Model\Mail\MailTemplateData $mailTemplateData
     * @param int $domainId
     */
    private function createMailTemplate(
        ObjectManager $manager,
        string $name,
        MailTemplateData $mailTemplateData,
        int $domainId,
    ): void {
        $repository = $manager->getRepository(MailTemplate::class);

        $mailTemplate = $repository->findOneBy([
            'name' => $name,
            'domainId' => $domainId,
        ]);

        $mailTemplateData->body = <<<EOT
            <div style="box-sizing: border-box;">
                <div class="gjs-text-ckeditor">{$mailTemplateData->body}</div>
            </div>
        EOT;

        if ($mailTemplate === null) {
            $mailTemplate = $this->mailTemplateFactory->create($name, $domainId, $mailTemplateData);
        } else {
            $mailTemplate->edit($mailTemplateData);
        }

        $manager->persist($mailTemplate);
        $manager->flush();
    }

    /**
     * @return string[]
     */
    #[Override]
    public function getDependencies(): array
    {
        return [
            TransportDataFixture::class,
            PaymentDataFixture::class,
            OrderStatusDataFixture::class,
        ];
    }

    /**
     * @param string $locale
     * @param \Doctrine\Persistence\ObjectManager $manager
     * @param int $domainId
     */
    private function createOrderPlacedMailTemplate(string $locale, ObjectManager $manager, int $domainId): void
    {
        $mailTemplateData = $this->mailTemplateDataFactory->create();
        $mailTemplateData->sendMail = true;

        $mailTemplateData->subject = t(
            'Your order no. {number} has been placed',
            [],
            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
            $locale,
        );
        $mailTemplateData->body = t('
            <h1>Your order has been placed successfully</h1>
            Dear customer,<br/>
            <br/>
            you will be contacted when the order state changes.<br/>
            <br/>
            Order number: <a href="{order_detail_url}" tabindex="0">{number}</a><br/>
            Date and time of creation: {date}<br/>
            {note}<br/>
            {transport_info}<br/>
            {transport_instructions}<br/>
            {payment_info}<br/>
            {payment_instructions}<br/>
            {products}
            <h3 style="text-align: right; margin: 0;">Total price including VAT: <span style="white-space: nowrap;">{total_price_with_vat}</span></h3>
            {rounding_info}<br/>
            {addresses}
            <a style="margin:0.75rem auto;display:flex;height:fit-content;width:fit-content;cursor:pointer;align-items:center;justify-content:center;gap:0.5rem;border-radius:0.5rem;border:2px solid #00C8B7;background-color:#00C8B7;padding:7px 12px;text-align:center;font-weight:500;line-height:18px;text-decoration:none;outline:none;transition:all 0.2s ease;color:#fff;"
                data-link-position="center"
                class="gjs-button-link button-link-position-center"
                title="Order detail"
                href="{order_detail_url}"
            >
                <div class="gjs-text-ckeditor text" data-gjs-type="text">Order detail</div>
            </a>
            <br/>            
            ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $mailTemplateData->orderStatus = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW, OrderStatus::class);

        $this->createMailTemplate($manager, 'order_status_1', $mailTemplateData, $domainId);
    }

    /**
     * @param string $locale
     * @param \Doctrine\Persistence\ObjectManager $manager
     * @param int $domainId
     */
    private function createOrderProcessedMailTemplate(string $locale, ObjectManager $manager, int $domainId): void
    {
        $mailTemplateData = $this->mailTemplateDataFactory->create();
        $mailTemplateData->sendMail = false;

        $mailTemplateData->subject = t('
            Your order no. {number} is being processed
            ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $mailTemplateData->body = t('
            <h1>Your order is being processed</h1>
            Dear customer,<br/>
            <br/>
            we have started processing your order.<br/>
            <br/>
            Order number: <a href="{order_detail_url}" tabindex="0">{number}</a><br/>
            Date and time of creation: {date}<br/>
            {note}<br/>
            {transport_info}<br/>
            {transport_instructions}<br/>
            {payment_info}<br/>
            {payment_instructions}<br/>
            {products}
            <h3 style="text-align: right; margin: 0;">Total price including VAT: <span style="white-space: nowrap;">{total_price_with_vat}</span></h3>
            {rounding_info}<br/>
            {addresses}
            ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $mailTemplateData->orderStatus = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_IN_PROGRESS, OrderStatus::class);

        $this->createMailTemplate($manager, 'order_status_2', $mailTemplateData, $domainId);
    }

    /**
     * @param string $locale
     * @param \Doctrine\Persistence\ObjectManager $manager
     * @param int $domainId
     */
    private function createOrderFinishedMailTemplate(string $locale, ObjectManager $manager, int $domainId): void
    {
        $mailTemplateData = $this->mailTemplateDataFactory->create();
        $mailTemplateData->sendMail = false;

        $mailTemplateData->subject = t('
            Your order no. {number} has been completed
            ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $mailTemplateData->body = t('
            <h1>Your order has been completed</h1>
            Dear customer,<br/>
            <br/>
            your order has been successfully completed.<br/>
            <br/>
            Order number: <a href="{order_detail_url}" tabindex="0">{number}</a><br/>
            Date and time of creation: {date}<br/>
            {note}<br/>
            {transport_info}<br/>
            {transport_instructions}<br/>
            {payment_info}<br/>
            {payment_instructions}<br/>
            {products}
            <h3 style="text-align: right; margin: 0;">Total price including VAT: <span style="white-space: nowrap;">{total_price_with_vat}</span></h3>
            {rounding_info}<br/>
            {addresses}
            ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $mailTemplateData->orderStatus = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_DONE, OrderStatus::class);

        $this->createMailTemplate($manager, 'order_status_3', $mailTemplateData, $domainId);
    }

    /**
     * @param string $locale
     * @param \Doctrine\Persistence\ObjectManager $manager
     * @param int $domainId
     */
    private function createOrderCanceledMailTemplate(string $locale, ObjectManager $manager, int $domainId): void
    {
        $mailTemplateData = $this->mailTemplateDataFactory->create();
        $mailTemplateData->sendMail = false;

        $mailTemplateData->subject = t('
            Your order no. {number} has been cancelled
            ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $mailTemplateData->body = t('
            <h1>Your order has been cancelled</h1>
            Dear customer,<br/>
            <br/>
            your order has been cancelled.<br/>
            <br/>
            Order number: <a href="{order_detail_url}" tabindex="0">{number}</a><br/>
            Date and time of creation: {date}<br/>
            {note}<br/>
            {transport_info}<br/>
            {transport_instructions}<br/>
            {payment_info}<br/>
            {payment_instructions}<br/>
            {products}
            <h3 style="text-align: right; margin: 0;">Total price including VAT: <span style="white-space: nowrap;">{total_price_with_vat}</span></h3>
            {rounding_info}<br/>
            {addresses}
            ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $mailTemplateData->orderStatus = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_CANCELED, OrderStatus::class);

        $this->createMailTemplate($manager, 'order_status_4', $mailTemplateData, $domainId);
    }

    /**
     * @param string $locale
     * @param \Doctrine\Persistence\ObjectManager $manager
     * @param int $domainId
     */
    private function createResetPasswordMailTemplate(string $locale, ObjectManager $manager, int $domainId): void
    {
        $mailTemplateData = $this->mailTemplateDataFactory->create();
        $mailTemplateData->sendMail = true;

        $mailTemplateData->subject = t('
            Reset password request
            ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $mailTemplateData->body = t('
            Dear customer,<br/><br/>
            you can set a new password following this <a href="{new_password_url}" tabindex="0">link</a>.<br/><br/>
            Best regards
            ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

        $this->createMailTemplate($manager, MailTemplate::RESET_PASSWORD_NAME, $mailTemplateData, $domainId);
    }

    /**
     * @param string $locale
     * @param \Doctrine\Persistence\ObjectManager $manager
     * @param int $domainId
     */
    private function createAdministratorResetPasswordMailTemplate(
        string $locale,
        ObjectManager $manager,
        int $domainId,
    ): void {
        $mailTemplateData = $this->mailTemplateDataFactory->create();
        $mailTemplateData->sendMail = true;

        $mailTemplateData->subject = t('
            Administrator reset password request
            ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $mailTemplateData->body = t('
            Dear administrator,<br/><br/>
            you can set a new password following this <a href="{new_password_url}" tabindex="0">link</a>.<br/><br/>
            Best regards
            ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

        $this->createMailTemplate($manager, ResetPasswordMail::MAIL_TEMPLATE_NAME, $mailTemplateData, $domainId);
    }

    /**
     * @param string $locale
     * @param \Doctrine\Persistence\ObjectManager $manager
     * @param int $domainId
     */
    private function createRegistrationCompleteMailTemplate(
        string $locale,
        ObjectManager $manager,
        int $domainId,
    ): void {
        $mailTemplateData = $this->mailTemplateDataFactory->create();
        $mailTemplateData->sendMail = true;

        $mailTemplateData->subject = t('
            Registration completed
            ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $mailTemplateData->body = t('
            Dear customer,<br/>
            <br/>
            your registration is completed.<br/>
            <br/>
            Name: {first_name} {last_name}<br />
            Email: {email}<br/>
            <br/>
            E-shop: <a href="{url}" tabindex="0">link</a><br />
            Login page: <a href="{login_page}" tabindex="0">Log in</a><br/>
            <br/>
            Best regards
            ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

        $this->createMailTemplate($manager, MailTemplate::REGISTRATION_CONFIRM_NAME, $mailTemplateData, $domainId);
    }

    /**
     * @param string $locale
     * @param \Doctrine\Persistence\ObjectManager $manager
     * @param int $domainId
     */
    private function createPersonalInformationOverviewMailTemplate(
        string $locale,
        ObjectManager $manager,
        int $domainId,
    ): void {
        $mailTemplateData = $this->mailTemplateDataFactory->create();
        $mailTemplateData->sendMail = true;

        $mailTemplateData->subject = t('
            Personal information overview - {domain}
            ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $mailTemplateData->body = t('
            Dear customer,<br/>
            <br/>
            based on your email {email}, we are sending you a link to your personal details. By clicking on the link below, you will be taken to a page listing all thepersonal details which we have in evidence in our online store {domain}.<br/>
            <br/>
            To overview your personal information please click <a href="{url}" tabindex="0">here</a>.<br/>
            The link is valid for next 24 hours.<br/>
            <br/>
            Best regards<br />
            Team of {domain}
            ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

        $this->createMailTemplate($manager, MailTemplate::PERSONAL_DATA_ACCESS_NAME, $mailTemplateData, $domainId);
    }

    /**
     * @param string $locale
     * @param \Doctrine\Persistence\ObjectManager $manager
     * @param int $domainId
     */
    private function createPersonalInformationExportMailTemplate(
        string $locale,
        ObjectManager $manager,
        int $domainId,
    ): void {
        $mailTemplateData = $this->mailTemplateDataFactory->create();
        $mailTemplateData->sendMail = true;

        $mailTemplateData->subject = t('
            Personal information export - {domain}
            ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $mailTemplateData->body = t('
            Dear customer,<br/>
            based on your email {email}, we are sending you a link where you can download your personal details registered on our online store in readable format. Clicking on the link will take you to a page where you’ll be able to download these informations, which we have in evidence in our online store {domain}.<br/>
            <br/>
            To download your personal information please click <a href="{url}" tabindex="0">here</a>.<br/>
            The link is valid for next 24 hours.<br/>
            <br/>
            Best regards<br />
            Team of {domain}
            
            ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

        $this->createMailTemplate($manager, MailTemplate::PERSONAL_DATA_EXPORT_NAME, $mailTemplateData, $domainId);
    }

    /**
     * @param string $locale
     * @param \Doctrine\Persistence\ObjectManager $manager
     * @param int $domainId
     */
    private function createCustomerActivationMailTemplate(string $locale, ObjectManager $manager, int $domainId): void
    {
        $mailTemplateData = $this->mailTemplateDataFactory->create();
        $mailTemplateData->sendMail = true;

        $mailTemplateData->subject = t('
            Customer activation
            ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $mailTemplateData->body = t('
            Dear customer,<br/>
            <br/>
            you can finish registration and set new password via this <a href="{activation_url}" tabindex="0">link</a>.<br/>
            <br/>
            Best regards
            ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

        $this->createMailTemplate($manager, CustomerActivationMail::CUSTOMER_ACTIVATION_NAME, $mailTemplateData, $domainId);
    }
}
