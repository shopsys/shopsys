<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20240325165512 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    private const string LOCALE_EN = 'en';

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $locale = $this->getDomainLocale($domainId);

            if ($locale !== self::LOCALE_EN) {
                continue;
            }

            $this->sql('UPDATE friendly_urls SET slug = :slug WHERE slug = :badSlug AND domain_id = :domainId', [
                'slug' => 'reset-password',
                'badSlug' => 'forgot-password',
                'domainId' => $domainId,
            ]);
            $this->sql('UPDATE friendly_urls SET slug = :slug WHERE slug = :badSlug AND domain_id = :domainId', [
                'slug' => 'brands-overview',
                'badSlug' => 'brands',
                'domainId' => $domainId,
            ]);
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
