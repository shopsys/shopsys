<?php

namespace Shopsys\FrameworkBundle\Migrations;

use PDO;

/**
 * @deprecated Trait is deprecated and will be removed in next major version. Use methods from Shopsys\FrameworkBundle\Migrations\AbstractMigration instead.
 * This trait can be used in classes
 * that extend \Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration.
 */
trait MultidomainMigrationTrait
{
    /**
     * @deprecated use AbstractMigration::getCreatedDomainIds() instead
     * @return int[]
     */
    protected function getAllDomainIds()
    {
        @trigger_error(
            sprintf(
                'Method "%s" is deprecated and will be removed in next major version. Use method "getCreatedDomainIds()" from "%s" instead.',
                __METHOD__,
                AbstractMigration::class
            ),
            E_USER_DEPRECATED
        );

        return $this
            ->sql('SELECT domain_id FROM setting_values WHERE name = :baseUrl', ['baseUrl' => 'baseUrl'])
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @deprecated use AbstractMigration::getDomainLocale() instead
     * @param int $domainId
     * @return string
     */
    protected function getDomainLocale($domainId)
    {
        @trigger_error(
            sprintf(
                'Method "%s" is deprecated and will be removed in next major version. Use method from "%s" instead.',
                __METHOD__,
                AbstractMigration::class
            ),
            E_USER_DEPRECATED
        );

        return $this
            ->sql('SELECT get_domain_locale(:domainId)', ['domainId' => $domainId])
            ->fetchColumn();
    }
}
