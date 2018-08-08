<?php

namespace Shopsys\FrameworkBundle\Migrations;

use PDO;
use Shopsys\FrameworkBundle\Component\Setting\Setting;

/**
 * This trait can be used in classes
 * that extend \Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration.
 */
trait MultidomainMigrationTrait
{
    /**
     * @return int[]
     */
    protected function getAllDomainIds(): array
    {
        return $this
            ->sql('SELECT domain_id FROM setting_values WHERE name = :baseUrl', ['baseUrl' => Setting::BASE_URL])
            ->fetchAll(PDO::FETCH_COLUMN, 'domain_id');
    }
    
    protected function getDomainLocale(int $domainId): string
    {
        return $this
            ->sql('SELECT get_domain_locale(:domainId)', ['domainId' => $domainId])
            ->fetchColumn();
    }
}
