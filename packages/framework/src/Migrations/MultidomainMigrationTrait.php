<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

/**
 * This trait can be used in classes
 * that extend \Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration.
 */
trait MultidomainMigrationTrait
{
    /**
     * @return int[]
     */
    protected function getAllDomainIds()
    {
        return $this
            ->sql('SELECT domain_id FROM setting_values WHERE name = :baseUrl', ['baseUrl' => 'baseUrl'])
            ->fetchFirstColumn();
    }

    /**
     * @param int $domainId
     * @return string
     */
    protected function getDomainLocale($domainId)
    {
        return $this
            ->sql('SELECT get_domain_locale(:domainId)', ['domainId' => $domainId])
            ->fetchOne();
    }
}
