<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20261005120000 extends AbstractMigration implements DomainAwareInterface
{
    use MailTemplateMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $domainLocale = $this->getDomainLocale($domainId);

            $this->updateMailTemplateBody(
                'watchdog_mail',
                $this->wrapMailTemplateBodyForGrapesJs(
                    t(
                        'Dear Customer,<br /><br />
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
                        <hr> If you have any questions or need assistance, don’t hesitate to contact us.',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $domainLocale,
                    ),
                ),
                $domainId,
            );

            $this->updateMailTemplateBody(
                'gift_voucher',
                $this->wrapMailTemplateBodyForGrapesJs(
                    t(
                        'Dear customer,<br /><br />
                        thank you for your purchase. Your gift voucher is attached to this email as a PDF file.<br /><br />
                        To redeem the voucher, enter its code in the cart into the field for discount coupons and gift vouchers. The voucher applies to the entire assortment including transport and payment costs and can be used only once, in its full value.<br /><br />
                        Best regards',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $domainLocale,
                    ),
                ),
                $domainId,
            );
        }
    }

    private function updateMailTemplateBody(string $mailTemplateName, string $body, int $domainId): void
    {
        $this->sql(
            'UPDATE mail_templates SET body = :body WHERE name = :mailTemplateName AND domain_id = :domainId',
            [
                'body' => $body,
                'mailTemplateName' => $mailTemplateName,
                'domainId' => $domainId,
            ],
        );
    }
}
