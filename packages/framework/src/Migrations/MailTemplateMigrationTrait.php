<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

trait MailTemplateMigrationTrait
{
    use MultidomainMigrationTrait;

    /**
     * Inserts a mail template row (name + send_mail) for every domain that does not yet have one.
     * Subject and body are expected to be set by a subsequent UPDATE, typically localized per domain.
     */
    protected function insertMailTemplateIfNotExist(string $mailTemplateName, bool $sendMail = true): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $mailTemplateCount = $this->sqlQuery(
                'SELECT count(*) FROM mail_templates WHERE name = :mailTemplateName AND domain_id = :domainId',
                [
                    'mailTemplateName' => $mailTemplateName,
                    'domainId' => $domainId,
                ],
            )->fetchOne();

            if ($mailTemplateCount > 0) {
                continue;
            }

            $this->sql(
                'INSERT INTO mail_templates (name, domain_id, send_mail) VALUES (:mailTemplateName, :domainId, :sendMail)',
                [
                    'mailTemplateName' => $mailTemplateName,
                    'domainId' => $domainId,
                    'sendMail' => $sendMail,
                ],
            );
        }
    }

    /**
     * Wraps a mail template body into the format required by GrapesJs, matching the historical
     * output of the demo data fixture (multi-line outer wrapper, inner content whitespace-collapsed).
     */
    protected function wrapMailTemplateBodyForGrapesJs(string $body): string
    {
        $normalizedBody = trim(preg_replace('~\s+~u', ' ', $body));

        return "    <div style=\"box-sizing: border-box;\">\n"
            . "        <div class=\"gjs-text-ckeditor\">{$normalizedBody}</div>\n"
            . '    </div>';
    }
}
