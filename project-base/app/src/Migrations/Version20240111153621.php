<?php

declare(strict_types=1);

namespace App\Migrations;

use DateTime;
use DateTimeZone;
use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Migrations\MultidomainMigrationTrait;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20240111153621 extends AbstractMigration implements ContainerAwareInterface
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

        $this->sql(
            sprintf('UPDATE adverts SET datetime_visible_from = datetime_visible_from - INTERVAL \'%d hour\' WHERE datetime_visible_from IS NOT NULL;', $timeOffsetInHours),
        );
        $this->sql(
            sprintf('UPDATE adverts SET datetime_visible_to = datetime_visible_to - INTERVAL \'%d hour\' WHERE datetime_visible_to IS NOT NULL;', $timeOffsetInHours),
        );

        $this->sql(
            sprintf('UPDATE notification_bars SET validity_from = validity_from - INTERVAL \'%d hour\' WHERE validity_from IS NOT NULL;', $timeOffsetInHours),
        );
        $this->sql(
            sprintf('UPDATE notification_bars SET validity_to = validity_to - INTERVAL \'%d hour\' WHERE validity_to IS NOT NULL;', $timeOffsetInHours),
        );

        $this->sql(
            sprintf('UPDATE slider_items SET datetime_visible_from = datetime_visible_from - INTERVAL \'%d hour\' WHERE datetime_visible_from IS NOT NULL;', $timeOffsetInHours),
        );
        $this->sql(
            sprintf('UPDATE slider_items SET datetime_visible_to = datetime_visible_to - INTERVAL \'%d hour\' WHERE datetime_visible_to IS NOT NULL;', $timeOffsetInHours),
        );
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
