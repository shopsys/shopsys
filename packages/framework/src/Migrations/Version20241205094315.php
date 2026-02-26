<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20241205094315 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        $this->createMailTemplateIfNotExist('watchdog_mail');

        foreach ($this->getAllDomainIds() as $domainId) {
            $domainLocale = $this->getDomainLocale($domainId);

            $this->updateMailTemplate(
                'watchdog_mail',
                t('Your watched product is back in stock!', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainLocale),
                t('<div style="box-sizing: border-box; padding: 10px;">
                                <div class="gjs-text-ckeditor"> <p>Dear Customer,</p></div>
                                <div class="gjs-text-ckeditor"> <p>We are excited to let you know that the product you added to your watchlist is now back in stock:</p></div>
                                <div class="gjs-text-ckeditor"><h2>{product_name}</h2></div>
                                <div class="gjs-text-ckeditor"> 
                                    <img data-gjs-type="mail-custom-image-with-variable"  draggable="true" alt="{product_name}" src="" path="{product_image}" style="display:block;margin:auto" class="mail-custom-image-with-variable gjs-plh-image gjs-selected">
                                </div>
                                <div class="gjs-text-ckeditor"><p style="text-align: center; font-size: 18px; font-weight: bold;">
                                    Currently available: {product_quantity}
                                </p></div>
                                <div class="gjs-text-ckeditor"><p>Don’t wait too long—supplies might be limited! Click the button below to secure your item:</p></div>
                                <div class="gjs-text-ckeditor">
                                    <div style="text-align: center; margin: 20px 0;">
                                        <a data-cke-saved-href="{product_url}" 
                                            href="{product_url}"
                                            style="display: inline-block; padding: 15px 30px; font-size: 16px; color: #fff; background-color: #00c8b7; text-decoration: none; border-radius: 5px;"
                                            tabindex="0">
                                            Buy Now
                                        </a>
                                    </div>
                                </div>
                                <div class="gjs-text-ckeditor"><p>Thank you for using our services, and we wish you a pleasant shopping experience!</p></div>
                                <div class="gjs-text-ckeditor"><p>If you need immediate assistance or have additional questions, feel free to reply to this email or contact us.</p></div>
                                <div class="gjs-text-ckeditor"><p>Best regards</p></div>
                                <hr>
                                <div class="gjs-text-ckeditor"><p>If you have any questions or need assistance, don’t hesitate to contact us.</p></div>
                            </div>
                        </div>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainLocale),
                $domainId,
            );
        }
    }

    private function createMailTemplateIfNotExist(
        string $mailTemplateName,
    ): void {
        foreach ($this->getAllDomainIds() as $domainId) {
            $mailTemplateCount = $this->sqlQuery(
                'SELECT count(*) FROM mail_templates WHERE name = :mailTemplateName and domain_id = :domainId',
                [
                    'mailTemplateName' => $mailTemplateName,
                    'domainId' => $domainId,
                ],
            )->fetchOne();

            if ($mailTemplateCount !== 0) {
                continue;
            }

            $this->sql(
                'INSERT INTO mail_templates (name, domain_id, send_mail) VALUES (:mailTemplateName, :domainId, :sendMail)',
                [
                    'mailTemplateName' => $mailTemplateName,
                    'domainId' => $domainId,
                    'sendMail' => true,
                ],
            );
        }
    }

    private function updateMailTemplate(string $mailTemplateName, string $subject, string $body, int $domainId): void
    {
        $this->sql(
            'UPDATE mail_templates SET subject = :subject, body = :body WHERE name = :mailTemplateName AND domain_id = :domainId',
            [
                'subject' => $subject,
                'body' => $body,
                'mailTemplateName' => $mailTemplateName,
                'domainId' => $domainId,
            ],
        );
    }
}
