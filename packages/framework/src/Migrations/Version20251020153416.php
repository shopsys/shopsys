<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20251020153416 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainConfigs() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();

            $withdrawalInstructions = t(
                '<p>We acknowledge your withdrawal from the contract for order number <b>{order_number}</b>.</p>
                    <p>We will contact you in the following days regarding the next steps, including the return of the goods.</p>
                    <p><a href="{order_detail_url}" tabindex="0">Show order detail</a></p>',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );

            $this->sql(
                'INSERT INTO setting_values (name, domain_id, value, type) VALUES
                    (:withdrawalDeadlineDays, :domainId, :deadlineValue, :integerType),
                    (:withdrawalInstructions, :domainId, :instructionsValue, :stringType)',
                [
                    'withdrawalDeadlineDays' => 'withdrawalDeadlineDays',
                    'withdrawalInstructions' => 'withdrawalInstructions',
                    'domainId' => $domainId,
                    'deadlineValue' => '14',
                    'integerType' => 'integer',
                    'instructionsValue' => $withdrawalInstructions,
                    'stringType' => 'string',
                ],
            );
        }
    }
}
