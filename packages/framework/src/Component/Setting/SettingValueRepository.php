<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Setting;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;

class SettingValueRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getSettingValueRepository()
    {
        return $this->em->getRepository(SettingValue::class);
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Setting\SettingValue[]
     */
    public function getAllByDomainId($domainId)
    {
        return $this->getSettingValueRepository()->findBy(['domainId' => $domainId]);
    }

    /**
     * @param int $fromDomainId
     * @param int $toDomainId
     */
    public function copyAllMultidomainSettings($fromDomainId, $toDomainId)
    {
        $this->em->getConnection()->executeStatement(
            'INSERT INTO setting_values (name, value, type, domain_id)
            SELECT name, value, type, :toDomainId
            FROM setting_values AS st
            WHERE domain_id = :fromDomainId
                AND EXISTS (
                    SELECT 1
                    FROM setting_values
                    WHERE domain_id IS NOT NULL
                        AND domain_id != :commonDomainId
                )
                AND NOT EXISTS (
                    SELECT 1
                    FROM setting_values
                    WHERE domain_id = :toDomainId AND name = st.name
                )',
            [
                'toDomainId' => $toDomainId,
                'fromDomainId' => $fromDomainId,
                'commonDomainId' => SettingValue::DOMAIN_ID_COMMON,
            ],
            [
                'toDomainId' => Types::INTEGER,
                'fromDomainId' => Types::INTEGER,
                'commonDomainId' => Types::INTEGER,
            ],
        );
    }
}
