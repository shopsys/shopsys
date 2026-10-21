<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20261021140000 extends AbstractMigration implements DomainAwareInterface
{
    use MailTemplateMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            foreach ($this->getOriginalBodies($this->getDomainLocale($domainId)) as $name => $previousBody) {
                // Only update shipped defaults; custom content and branding belong to the shop.
                $this->sql(
                    'UPDATE mail_templates SET body = :body WHERE name = :name AND domain_id = :domainId AND body = :previousBody',
                    [
                        'body' => $this->updateBody($previousBody),
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
    private function getOriginalBodies(string $locale): array
    {
        return [
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

    private function updateBody(string $body): string
    {
        $body = str_replace('#1a73e8', '#006bd6', $body);
        // Explicit dimensions also constrain product images in Word-based Outlook.
        $imageWidth = str_contains($body, '{product_image}') ? 200 : 100;
        $body = str_replace('<img ', '<img width="' . $imageWidth . '" ', $body);
        $body = str_replace('max-width: 200px; max-height: 100px;', 'width:100px; max-width:100%;', $body);
        $body = str_replace('style="display:block;margin:auto"', 'style="display:block; width:200px; max-width:100%; height:auto; margin:auto;"', $body);

        foreach (['{product_url}', '{adminInquiryDetailUrl}'] as $url) {
            $anchorPattern = '<a\b[^>]*href="' . preg_quote($url, '~') . '"[^>]*>(.*?)</a>';
            $pattern = $url === '{adminInquiryDetailUrl}' ? '<p>' . $anchorPattern . '</p>' : $anchorPattern;
            $body = preg_replace_callback(
                '~' . $pattern . '~s',
                fn (array $matches): string => $this->getButton($url, trim($matches[1])),
                $body,
            );
        }

        return $body;
    }

    private function getButton(string $url, string $label): string
    {
        return <<<HTML
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center" style="padding:12px 0 24px;">
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
