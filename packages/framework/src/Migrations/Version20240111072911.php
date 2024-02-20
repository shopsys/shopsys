<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use DateTime;
use DateTimeZone;
use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20240111072911 extends AbstractMigration implements ContainerAwareInterface
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
            sprintf('UPDATE blog_articles SET publish_date = publish_date - INTERVAL \'%d hour\';', $timeOffsetInHours),
        );
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
