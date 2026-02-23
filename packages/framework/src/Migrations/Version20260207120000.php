<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlSlugNormalizer;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20260207120000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $friendlyUrlsResult = $this->sql(
            'SELECT domain_id, slug, route_name, entity_id, main, redirect_to, redirect_code, last_modification
             FROM friendly_urls',
        );

        while (($row = $friendlyUrlsResult->fetchAssociative()) !== false) {
            $normalizedSlug = FriendlyUrlSlugNormalizer::normalize($row['slug']);

            if ($normalizedSlug === $row['slug']) {
                continue;
            }

            $this->sql(
                'DELETE FROM friendly_urls WHERE domain_id = :domainId AND slug = :slug',
                [
                    'domainId' => $row['domain_id'],
                    'slug' => $row['slug'],
                ],
            );

            $this->sql(
                'INSERT INTO friendly_urls (domain_id, slug, route_name, entity_id, main, redirect_to, redirect_code, last_modification)
                 VALUES (:domainId, :slug, :routeName, :entityId, :main, :redirectTo, :redirectCode, :lastModification)
                 ON CONFLICT DO NOTHING',
                [
                    'domainId' => $row['domain_id'],
                    'slug' => $normalizedSlug,
                    'routeName' => $row['route_name'],
                    'entityId' => $row['entity_id'],
                    'main' => $row['main'],
                    'redirectTo' => $row['redirect_to'],
                    'redirectCode' => $row['redirect_code'],
                    'lastModification' => $row['last_modification'],
                ],
            );
        }
    }
}
