<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use DateTime;
use DateTimeZone;
use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20250121135309 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20240111153621')) {
            $this->convertAdvertDates();
            $this->convertNotificationBarDates();
            $this->convertSliderItemDates();
        }
    }

    private function convertDateTime(?string $dateTimeString): ?DateTime
    {
        if ($dateTimeString === null) {
            return null;
        }

        $dateTime = new DateTime(
            $dateTimeString,
            new DateTimeZone('Europe/Prague'),
        );
        $dateTime->setTimezone(new DateTimeZone('UTC'));

        return $dateTime;
    }

    private function convertAdvertDates(): void
    {
        $adverts = $this->sql('SELECT id, datetime_visible_from, datetime_visible_to FROM adverts')->fetchAllAssociative();

        foreach ($adverts as $advert) {
            $visibleFrom = $this->convertDateTime($advert['datetime_visible_from']);
            $visibleTo = $this->convertDateTime($advert['datetime_visible_to']);

            $this->sql(
                'UPDATE adverts SET datetime_visible_from = :visibleFrom, datetime_visible_to = :visibleTo  WHERE id = :id',
                [
                    'visibleFrom' => $visibleFrom,
                    'visibleTo' => $visibleTo,
                    'id' => $advert['id'],
                ],
                [
                    'visibleFrom' => 'datetime',
                    'visibleTo' => 'datetime',
                    'id' => 'integer',
                ],
            );
        }
    }

    private function convertNotificationBarDates(): void
    {
        $notificationBars = $this->sql('SELECT id, validity_from, validity_to FROM notification_bars')
            ->fetchAllAssociative();

        foreach ($notificationBars as $notificationBar) {
            $validityFrom = $this->convertDateTime($notificationBar['validity_from']);
            $validityTo = $this->convertDateTime($notificationBar['validity_to']);

            $this->sql(
                'UPDATE notification_bars SET validity_from = :validityFrom, validity_to = :validityTo  WHERE id = :id',
                [
                    'validityFrom' => $validityFrom,
                    'validityTo' => $validityTo,
                    'id' => $notificationBar['id'],
                ],
                [
                    'validityFrom' => 'datetime',
                    'validityTo' => 'datetime',
                    'id' => 'integer',
                ],
            );
        }
    }

    private function convertSliderItemDates(): void
    {
        $sliderItems = $this->sql('SELECT id, datetime_visible_from, datetime_visible_to FROM slider_items')
            ->fetchAllAssociative();

        foreach ($sliderItems as $sliderItem) {
            $visibleFrom = $this->convertDateTime($sliderItem['datetime_visible_from']);
            $visibleTo = $this->convertDateTime($sliderItem['datetime_visible_to']);

            $this->sql(
                'UPDATE slider_items SET datetime_visible_from = :visibleFrom, datetime_visible_to = :visibleTo  WHERE id = :id',
                [
                    'visibleFrom' => $visibleFrom,
                    'visibleTo' => $visibleTo,
                    'id' => $sliderItem['id'],
                ],
                [
                    'visibleFrom' => 'datetime',
                    'visibleTo' => 'datetime',
                    'id' => 'integer',
                ],
            );
        }
    }
}
