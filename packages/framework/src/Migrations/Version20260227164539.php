<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260227164539 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE blog_article_domains ADD status VARCHAR(25) DEFAULT NULL');
        $this->sql('
            UPDATE blog_article_domains bad
            SET status = CASE WHEN ba.hidden = true THEN \'draft\' ELSE \'published\' END
            FROM blog_articles ba
            WHERE bad.blog_article_id = ba.id
        ');
        $this->sql('ALTER TABLE blog_article_domains ALTER COLUMN status SET NOT NULL');

        $this->sql('ALTER TABLE blog_article_domains ADD publish_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->sql('
            UPDATE blog_article_domains bad
            SET publish_date = ba.publish_date
            FROM blog_articles ba
            WHERE bad.blog_article_id = ba.id
        ');

        $this->sql('ALTER TABLE blog_articles DROP COLUMN publish_date');
        $this->sql('ALTER TABLE blog_articles DROP COLUMN hidden');
    }
}
