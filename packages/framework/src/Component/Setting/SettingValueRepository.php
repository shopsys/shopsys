<?php

namespace Shopsys\FrameworkBundle\Component\Setting;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

class SettingValueRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function getSettingValueRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(SettingValue::class);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Setting\SettingValue[]
     */
    public function getAllByDomainId(int $domainId): array
    {
        return $this->getSettingValueRepository()->findBy(['domainId' => $domainId]);
    }
    
    public function copyAllMultidomainSettings(int $fromDomainId, int $toDomainId): void
    {
        $query = $this->em->createNativeQuery(
            'INSERT INTO setting_values (name, value, type, domain_id)
            SELECT name, value, type, :toDomainId
            FROM setting_values
            WHERE domain_id = :fromDomainId
                AND EXISTS (
                    SELECT 1
                    FROM setting_values
                    WHERE domain_id IS NOT NULL
                        AND domain_id != :commonDomainId
                )',
            new ResultSetMapping()
        );
        $query->execute([
            'toDomainId' => $toDomainId,
            'fromDomainId' => $fromDomainId,
            'commonDomainId' => SettingValue::DOMAIN_ID_COMMON,
        ]);
    }
}
