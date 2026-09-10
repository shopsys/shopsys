<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20180603135347 extends AbstractMigration implements DomainAwareInterface
{
    use MailTemplateMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        $this->insertMailTemplateIfNotExist('order_status_1');
        $this->insertMailTemplateIfNotExist('order_status_2');
        $this->insertMailTemplateIfNotExist('order_status_3');
        $this->insertMailTemplateIfNotExist('order_status_4');
        $this->insertMailTemplateIfNotExist(MailTemplate::REGISTRATION_CONFIRM_NAME);
        $this->insertMailTemplateIfNotExist(MailTemplate::RESET_PASSWORD_NAME);

        foreach ($this->getAllDomainConfigs() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();

            $this->updateMailTemplate(
                'order_status_1',
                $domainId,
                t('Your order no. {number} has been placed', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                $this->wrapMailTemplateBodyForGrapesJs(t('
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
                    {gift_vouchers_info}
                    {rounding_info}<br/>
                    {addresses}
                    <a style="margin:0.75rem auto;display:flex;height:fit-content;width:fit-content;cursor:pointer;align-items:center;justify-content:center;gap:0.5rem;border-radius:0.5rem;border:2px solid #00C8B7;background-color:#00C8B7;padding:7px 12px;text-align:center;font-weight:500;line-height:18px;text-decoration:none;outline:none;transition:all 0.2s ease;color:#fff;"
                        data-gjs-type="button-link"
                        data-link-position="center"
                        title="Order detail"
                        href="{order_detail_url}"
                    >Order detail</a>
                ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)),
            );

            $this->updateMailTemplate(
                'order_status_2',
                $domainId,
                t('Your order no. {number} is being processed', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                $this->wrapMailTemplateBodyForGrapesJs(t('
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
                    {gift_vouchers_info}
                    {rounding_info}<br/>
                    {addresses}
                ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)),
            );

            $this->updateMailTemplate(
                'order_status_3',
                $domainId,
                t('Your order no. {number} has been completed', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                $this->wrapMailTemplateBodyForGrapesJs(t('
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
                    {gift_vouchers_info}
                    {rounding_info}<br/>
                    {addresses}
                ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)),
            );

            $this->updateMailTemplate(
                'order_status_4',
                $domainId,
                t('Your order no. {number} has been cancelled', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                $this->wrapMailTemplateBodyForGrapesJs(t('
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
                    {gift_vouchers_info}
                    {rounding_info}<br/>
                    {addresses}
                ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)),
            );

            $this->updateMailTemplate(
                MailTemplate::REGISTRATION_CONFIRM_NAME,
                $domainId,
                t('Registration completed', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                $this->wrapMailTemplateBodyForGrapesJs(t('
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
                ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)),
            );

            $this->updateMailTemplate(
                'reset_password',
                $domainId,
                t('Reset password request', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                $this->wrapMailTemplateBodyForGrapesJs(t('
                    Dear customer,<br/><br/>
                    you can set a new password following this <a href="{new_password_url}" tabindex="0">link</a>.<br/><br/>
                    Best regards
                ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)),
            );
        }
    }

    private function updateMailTemplate(string $mailTemplateName, int $domainId, string $subject, string $body): void
    {
        $this->sql(
            'UPDATE mail_templates SET subject = :subject, body = :body
             WHERE name = :mailTemplateName AND domain_id = :domainId',
            [
                'subject' => $subject,
                'body' => $body,
                'mailTemplateName' => $mailTemplateName,
                'domainId' => $domainId,
            ],
        );
    }
}
