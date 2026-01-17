<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use DateTime;
use DateTimeZone;
use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20240111072911 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        $blogArticles = $this->sql('SELECT id, publish_date FROM blog_articles')->fetchAllAssociative();

        foreach ($blogArticles as $blogArticle) {
            $dateTime = new DateTime(
                $blogArticle['publish_date'],
                new DateTimeZone('Europe/Prague'),
            );
            $dateTime->setTimezone(new DateTimeZone('UTC'));

            $this->sql(
                'UPDATE blog_articles SET publish_date = :publishDate WHERE id = :id',
                [
                    'publishDate' => $dateTime,
                    'id' => $blogArticle['id'],
                ],
                [
                    'publishDate' => 'datetime',
                    'id' => 'integer',
                ],
            );
        }
    }
}
