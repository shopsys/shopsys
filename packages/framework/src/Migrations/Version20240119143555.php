<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use DateTime;
use DateTimeZone;
use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20240119143555 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $firstDomainTimeZone = $this->getDomain()->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getDateTimeZone();
        $utcTimeZone = new DateTimeZone('UTC');
        $dateTime = new DateTime('now', $utcTimeZone);
        $timeOffsetInHours = timezone_offset_get($firstDomainTimeZone, $dateTime) / 3600;

        $this->sql(sprintf('UPDATE store_opening_hours
            SET first_opening_time = to_char((first_opening_time::time - INTERVAL \'%d hour\'), \'HH24:MI\')
            WHERE first_opening_time IS NOT NULL;
        ', $timeOffsetInHours));

        $this->sql(sprintf('UPDATE store_opening_hours
            SET first_closing_time = to_char((first_closing_time::time - INTERVAL \'%d hour\'), \'HH24:MI\')
            WHERE first_closing_time IS NOT NULL;
        ', $timeOffsetInHours));

        $this->sql(sprintf('UPDATE store_opening_hours
            SET second_opening_time = to_char((second_opening_time::time - INTERVAL \'%d hour\'), \'HH24:MI\')
            WHERE second_opening_time IS NOT NULL;
        ', $timeOffsetInHours));

        $this->sql(sprintf('UPDATE store_opening_hours
            SET second_closing_time = to_char((second_closing_time::time - INTERVAL \'%d hour\'), \'HH24:MI\')
            WHERE second_closing_time IS NOT NULL;
        ', $timeOffsetInHours));
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
