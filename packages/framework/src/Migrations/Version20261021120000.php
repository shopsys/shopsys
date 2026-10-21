<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20261021120000 extends AbstractMigration implements DomainAwareInterface
{
    use MailTemplateMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $locale = $this->getDomainLocale($domainId);
            $bodies = $this->getBodies($locale);

            foreach ($this->getPreviousBodies($locale) as $name => $previousBody) {
                $this->sql(
                    'UPDATE mail_templates SET body = :body WHERE name = :name AND domain_id = :domainId AND body = :previousBody',
                    [
                        'body' => $this->wrapMailTemplateBodyForGrapesJs($bodies[$name]),
                        'name' => $name,
                        'domainId' => $domainId,
                        'previousBody' => $previousBody,
                    ],
                );
            }
        }
    }

    /**
     * @return array<string, string>
     */
    private function getPreviousBodies(string $locale): array
    {
        return [
            'order_status_1' => $this->wrapMailTemplateBodyForGrapesJs(t('
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
            'order_status_2' => $this->wrapMailTemplateBodyForGrapesJs(t('
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
            'order_status_3' => $this->wrapMailTemplateBodyForGrapesJs(t('
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
            'order_status_4' => $this->wrapMailTemplateBodyForGrapesJs(t('
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
            'watchdog_mail' => $this->wrapMailTemplateBodyForGrapesJs(t('Dear Customer,<br /><br />
                        We are excited to let you know that the product you added to your watchlist is now back in stock:<br /><br />
                        <h2>{product_name}</h2>
                        <img data-gjs-type="mail-custom-image-with-variable" draggable="true" alt="{product_name}" src="" path="{product_image}" style="display:block;margin:auto" class="mail-custom-image-with-variable gjs-plh-image gjs-selected">
                        <p style="text-align: center; font-size: 18px; font-weight: bold;"> Currently available: {product_quantity} </p>
                        Don’t wait too long—supplies might be limited! Click the button below to secure your item:<br /><br />
                        <div style="text-align: center; margin: 20px 0;">
                        <a data-cke-saved-href="{product_url}" href="{product_url}" style="display: inline-block; padding: 15px 30px; font-size: 16px; color: #fff; background-color: #00c8b7; text-decoration: none; border-radius: 5px;" tabindex="0"> Buy Now </a>
                        </div>
                        Thank you for using our services, and we wish you a pleasant shopping experience!<br /><br />
                        If you need immediate assistance or have additional questions, feel free to reply to this email or contact us.<br /><br />
                        Best regards<br /><br />
                        <hr> If you have any questions or need assistance, don’t hesitate to contact us.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)),
            'product_inquiry_customer' => $this->wrapMailTemplateBodyForGrapesJs(t('<div style="font-family: Arial, sans-serif; line-height: 1.6;"><h1>Your inquiry has been placed</h1><p>Dear {fullName},</p><p>thank you for your interest! We will be reaching out to you shortly with further information.</p><p><strong>Inquired product:</strong></p><p><a data-cke-saved-href="{productUrl}" href="{productUrl}" style="color: #1a73e8; text-decoration: none" tabindex="0">{productName}</a> (Catalog number: {productCatnum})</p><p><img src="{productImageUrl}" alt="{productName}" style="max-width: 200px; max-height: 100px; height: auto;" /></p><p><strong>Your contact details:</strong></p><ul style="list-style-type: none; padding: 0"><li><strong>Name:</strong> {fullName}</li><li><strong>Email:</strong> <a href="mailto:{email}" tabindex="0">{email}</a></li><li><strong>Telephone:</strong> <a href="tel:{telephone}" tabindex="0">{telephone}</a></li><li><strong>Company name:</strong> {companyName}</li><li><strong>Company number:</strong> {companyNumber}</li><li><strong>Company tax number:</strong> {companyTaxNumber}</li></ul><p><strong>Note:</strong></p><blockquote style="border-left: 4px solid #ccc;margin: 0 0 1em;padding: 0.5em;font-style: italic;background-color: #f9f9f9;">{note}</blockquote><p>If you need immediate assistance or have additional questions, feel free to reply to this email or contact us.</p><p>Best regards</p></div>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)),
            'product_inquiry_admin' => $this->wrapMailTemplateBodyForGrapesJs(t('<div style="font-family: Arial, sans-serif; line-height: 1.6;"><p>A new product inquiry has just been received from <strong>{fullName}</strong>.</p><p>Please find the details below:</p><ul style="list-style-type: none; padding: 0;"><li><strong>Customer name:</strong> {fullName}</li><li><strong>Customer email:</strong> <a href="mailto:{email}" tabindex="0">{email}</a></li><li><strong>Customer telephone:</strong> <a href="tel:{telephone}" tabindex="0">{telephone}</a></li><li><strong>Company name:</strong> {companyName}</li><li><strong>Company number:</strong> {companyNumber}</li><li><strong>Company tax number:</strong> {companyTaxNumber}</li></ul><p><strong>Note from customer:</strong></p><blockquote style="border-left: 4px solid #ccc; margin: 0 0 1em; padding: 0.5em; font-style: italic; background-color: #f9f9f9;">{note}</blockquote><p><strong>Inquired product:</strong></p><p><a href="{productUrl}" style="color: #1a73e8; text-decoration: none;" tabindex="0">{productName}</a> (Catalog number: {productCatnum})</p><p><img src="{productImageUrl}" alt="{productName}" style="max-width: 200px; max-height: 100px; height: auto; border: 1px solid #ddd; padding: 4px;"></p><p>Please review the inquiry and take the necessary steps to follow up.</p><p>&nbsp;</p><p><a href="{adminInquiryDetailUrl}" style="background-color: #1a73e8; color: #fff; padding: 10px 15px; text-decoration: none; border-radius: 5px;" tabindex="0">Review Inquiry</a></p></div>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getBodies(string $locale): array
    {
        $watchdogButton = $this->getButton('{product_url}', t('Buy Now', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), '12px 0 24px');
        $inquiryAdminButton = $this->getButton('{adminInquiryDetailUrl}', t('Review Inquiry', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), '12px 0 24px');

        return [
            'order_status_1' => $this->getOrderBody(
                $locale,
                t('Thank you for your order', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                t('We have received your order. We will let you know when its status changes.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                $this->getOrderConfirmationPreheader($locale),
                $this->getButton('{order_detail_url}', t('View order', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), '0 0 24px'),
            ),
            'order_status_2' => $this->getOrderBody(
                $locale,
                t('Your order is being processed', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                t('We have started processing your order.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ),
            'order_status_3' => $this->getOrderBody(
                $locale,
                t('Your order has been completed', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                t('Your order has been successfully completed.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ),
            'order_status_4' => $this->getOrderBody(
                $locale,
                t('Your order has been cancelled', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                t('We have cancelled your order.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ),
            'watchdog_mail' => t('Dear Customer,<br /><br /> We are excited to let you know that the product you added to your watchlist is now back in stock:<br /><br /> <h2>{product_name}</h2> <img width="200" data-gjs-type="mail-custom-image-with-variable" draggable="true" alt="{product_name}" src="" path="{product_image}" style="display:block; width:200px; max-width:100%; height:auto; margin:auto;" class="mail-custom-image-with-variable gjs-plh-image gjs-selected"> <p style="text-align: center; font-size: 18px; font-weight: bold;"> Currently available: {product_quantity} </p> Don’t wait too long—supplies might be limited! Click the button below to secure your item:<br /><br />', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)
                . ' <div style="text-align: center; margin: 20px 0;"> ' . $watchdogButton . ' </div> '
                . t('Thank you for using our services, and we wish you a pleasant shopping experience!<br /><br /> If you need immediate assistance or have additional questions, feel free to reply to this email or contact us.<br /><br /> Best regards<br /><br /> <hr> If you have any questions or need assistance, don’t hesitate to contact us.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            'product_inquiry_customer' => t('<div style="font-family: Arial, sans-serif; line-height: 1.6;"><h1>Your inquiry has been placed</h1><p>Dear {fullName},</p><p>thank you for your interest! We will be reaching out to you shortly with further information.</p><p><strong>Inquired product:</strong></p><p><a data-cke-saved-href="{productUrl}" href="{productUrl}" style="color: #006bd6; text-decoration: none" tabindex="0">{productName}</a> (Catalog number: {productCatnum})</p><p><img width="100" src="{productImageUrl}" alt="{productName}" style="width:100px; max-width:100%; height: auto;" /></p><p><strong>Your contact details:</strong></p><ul style="list-style-type: none; padding: 0"><li><strong>Name:</strong> {fullName}</li><li><strong>Email:</strong> <a href="mailto:{email}" tabindex="0">{email}</a></li><li><strong>Telephone:</strong> <a href="tel:{telephone}" tabindex="0">{telephone}</a></li><li><strong>Company name:</strong> {companyName}</li><li><strong>Company number:</strong> {companyNumber}</li><li><strong>Company tax number:</strong> {companyTaxNumber}</li></ul><p><strong>Note:</strong></p><blockquote style="border-left: 4px solid #ccc;margin: 0 0 1em;padding: 0.5em;font-style: italic;background-color: #f9f9f9;">{note}</blockquote><p>If you need immediate assistance or have additional questions, feel free to reply to this email or contact us.</p><p>Best regards</p></div>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            'product_inquiry_admin' => '<div style="font-family: Arial, sans-serif; line-height: 1.6;">'
                . t('<p>A new product inquiry has just been received from <strong>{fullName}</strong>.</p><p>Please find the details below:</p><ul style="list-style-type: none; padding: 0;"><li><strong>Customer name:</strong> {fullName}</li><li><strong>Customer email:</strong> <a href="mailto:{email}" tabindex="0">{email}</a></li><li><strong>Customer telephone:</strong> <a href="tel:{telephone}" tabindex="0">{telephone}</a></li><li><strong>Company name:</strong> {companyName}</li><li><strong>Company number:</strong> {companyNumber}</li><li><strong>Company tax number:</strong> {companyTaxNumber}</li></ul><p><strong>Note from customer:</strong></p><blockquote style="border-left: 4px solid #ccc; margin: 0 0 1em; padding: 0.5em; font-style: italic; background-color: #f9f9f9;">{note}</blockquote><p><strong>Inquired product:</strong></p><p><a href="{productUrl}" style="color: #006bd6; text-decoration: none;" tabindex="0">{productName}</a> (Catalog number: {productCatnum})</p><p><img width="100" src="{productImageUrl}" alt="{productName}" style="width:100px; max-width:100%; height: auto; border: 1px solid #ddd; padding: 4px;"></p><p>Please review the inquiry and take the necessary steps to follow up.</p><p>&nbsp;</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)
                . $inquiryAdminButton
                . '</div>',
        ];
    }

    private function getOrderBody(
        string $locale,
        string $heading,
        string $intro,
        string $preheader = '',
        string $orderDetailButton = '',
    ): string {
        $number = t('Order number', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $date = t('Created on', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $total = t('Total including VAT', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

        return <<<HTML
            {$preheader}
            <h1 style="font-size:26px; color:#202139; margin:0 0 16px;">{$heading}</h1>
            <p style="margin:0 0 16px;">{$intro}</p>
            <p style="margin:0 0 24px; color:#385483;">{$number}: <a href="{order_detail_url}" style="color:#006bd6;">{number}</a><br>{$date}: {date}</p>
            {$orderDetailButton}
            {note}
            {products}
            {transport_info}
            {transport_instructions}
            {payment_info}
            {payment_instructions}
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin:12px 0 24px; border-top:2px solid #e5e5e5;"><tr>
                <td style="padding:16px 12px 0 0; font-weight:bold;">{$total}</td>
                <td align="right" style="padding:16px 0 0; font-size:22px; font-weight:bold; color:#006bd6; white-space:nowrap;">{total_price_with_vat}</td>
            </tr></table>
            {gift_vouchers_info}
            {rounding_info}
            {addresses}
            HTML;
    }

    private function getOrderConfirmationPreheader(string $locale): string
    {
        $preheader = t('We have received your order {number}. You can find its summary and status here.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

        return '<div style="display:none; font-size:1px; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden; mso-hide:all;">' . $preheader . '</div>';
    }

    private function getButton(string $url, string $label, string $padding): string
    {
        return <<<HTML
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding:{$padding};">
                <!--[if mso]>
                <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{$url}" style="height:48px;v-text-anchor:middle;width:240px;" arcsize="33%" stroke="f" fillcolor="#006bd6">
                    <w:anchorlock/><center style="color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:16px;font-weight:bold;">{$label}</center>
                </v:roundrect>
                <![endif]-->
                <!--[if !mso]><!-->
                <a href="{$url}" style="display:inline-block; width:240px; max-width:100%; box-sizing:border-box; background-color:#006bd6; border-radius:8px; color:#ffffff; font-size:16px; font-weight:bold; line-height:24px; padding:12px 8px; text-align:center; text-decoration:none; mso-hide:all;">{$label}</a>
                <!--<![endif]-->
            </td></tr></table>
            HTML;
    }
}
