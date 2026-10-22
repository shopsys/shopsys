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
            // Preserve shop-specific content; only replace the original default confirmation.
            $this->sql(
                'UPDATE mail_templates SET body = :body WHERE name = :name AND domain_id = :domainId AND body = :previousBody',
                [
                    'body' => $this->wrapMailTemplateBodyForGrapesJs($this->getBody($locale)),
                    'name' => 'order_status_1',
                    'domainId' => $domainId,
                    'previousBody' => $this->getPreviousBody($locale),
                ],
            );
        }
    }

    private function getPreviousBody(string $locale): string
    {
        return $this->wrapMailTemplateBodyForGrapesJs(t('
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
                ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale));
    }

    private function getBody(string $locale): string
    {
        $preheader = t('We have received your order {number}. You can find its summary and status here.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $heading = t('Thank you for your order', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $intro = t('We have received your order. We will let you know when its status changes.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $number = t('Order number', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $date = t('Created on', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $button = t('View order', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $total = t('Total including VAT', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

        return <<<HTML
            <div style="display:none; font-size:1px; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden; mso-hide:all;">{$preheader}</div>
            <h1 style="font-size:26px; color:#202139; margin:0 0 16px;">{$heading}</h1>
            <p style="margin:0 0 16px;">{$intro}</p>
            <p style="margin:0 0 24px; color:#385483;">{$number}: <a href="{order_detail_url}" style="color:#006bd6;">{number}</a><br>{$date}: {date}</p>
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding:0 0 24px;">
                <!--[if mso]>
                <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{order_detail_url}" style="height:48px;v-text-anchor:middle;width:240px;" arcsize="33%" stroke="f" fillcolor="#006bd6">
                    <w:anchorlock/><center style="color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:16px;font-weight:bold;">{$button}</center>
                </v:roundrect>
                <![endif]-->
                <!--[if !mso]><!-->
                <a href="{order_detail_url}" style="display:inline-block; width:240px; max-width:100%; box-sizing:border-box; background-color:#006bd6; border-radius:8px; color:#ffffff; font-size:16px; font-weight:bold; line-height:24px; padding:12px 8px; text-align:center; text-decoration:none; mso-hide:all;">{$button}</a>
                <!--<![endif]-->
            </td></tr></table>
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
}
