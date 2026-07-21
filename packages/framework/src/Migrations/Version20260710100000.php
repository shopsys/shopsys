<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\GiftVoucher\Mail\GiftVoucherMail;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260710100000 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $domainLocale = $this->getDomainLocale($domainId);

            $this->sql(
                'INSERT INTO mail_templates (name, domain_id, send_mail, subject, body)
                VALUES (:mailTemplateName, :domainId, :sendMail, :subject, :body)',
                [
                    'mailTemplateName' => GiftVoucherMail::GIFT_VOUCHER_MAIL_TEMPLATE_NAME,
                    'domainId' => $domainId,
                    'sendMail' => true,
                    'subject' => t('Your gift voucher', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainLocale),
                    'body' => t('<div style="box-sizing: border-box; padding: 10px;">
                                <div class="gjs-text-ckeditor"><p>Dear customer,</p></div>
                                <div class="gjs-text-ckeditor"><p>thank you for your purchase. Your gift voucher is attached to this email as a PDF file.</p></div>
                                <div class="gjs-text-ckeditor"><p>To redeem the voucher, enter its code in the cart into the field for discount coupons and gift vouchers. The voucher applies to the entire assortment including transport and payment costs and can be used only once, in its full value.</p></div>
                                <div class="gjs-text-ckeditor"><p>Best regards</p></div>
                            </div>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainLocale),
                ],
            );
        }
    }
}
