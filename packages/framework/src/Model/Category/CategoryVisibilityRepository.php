<?php

namespace Shopsys\FrameworkBundle\Model\Category;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class CategoryVisibilityRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRecalculationScheduler $categoryVisibilityRecalculationScheduler
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly Domain $domain,
        protected readonly CategoryVisibilityRecalculationScheduler $categoryVisibilityRecalculationScheduler,
    ) {
    }

    public function refreshCategoriesVisibility()
    {
        $domains = $this->domain->getAll();
        foreach ($domains as $domainConfig) {
            $this->refreshCategoriesVisibilityOnDomain($domainConfig);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     */
    protected function refreshCategoriesVisibilityOnDomain(DomainConfig $domainConfig)
    {
        $this->setRootCategoryVisibleOnDomain($domainConfig);

        $maxLevel = $this->getMaxLevelOnDomain($domainConfig);

        for ($level = 1; $level <= $maxLevel; $level++) {
            $this->refreshCategoriesVisibilityOnDomainAndLevel($domainConfig, $level);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     */
    protected function setRootCategoryVisibleOnDomain(DomainConfig $domainConfig)
    {
        $this->em->getConnection()->executeStatement(
            'UPDATE category_domains AS cd
                SET visible = TRUE

            FROM categories AS c
            WHERE c.id = cd.category_id
                AND cd.domain_id = :domainId
                AND c.parent_id IS NULL
            ',
            [
                'domainId' => $domainConfig->getId(),
            ],
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return int
     */
    protected function getMaxLevelOnDomain(DomainConfig $domainConfig)
    {
        return $this->em->getConnection()->fetchOne(
            'SELECT MAX(c.level)
            FROM categories c
            JOIN category_domains cd ON cd.category_id = c.id AND cd.domain_id = :domainId
            ',
            [
                'domainId' => $domainConfig->getId(),
            ],
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int $level
     */
    protected function refreshCategoriesVisibilityOnDomainAndLevel(DomainConfig $domainConfig, $level)
    {
        $this->em->getConnection()->executeStatement(
            'UPDATE category_domains AS cd
                SET visible = (
                    cd.enabled = TRUE
                    AND
                    ct.name IS NOT NULL
                    AND
                    parent_cd.visible = TRUE
                )

            FROM categories AS c
            LEFT JOIN category_translations ct ON ct.translatable_id = c.id AND ct.locale = :locale
            JOIN category_domains AS parent_cd ON parent_cd.category_id = c.parent_id AND parent_cd.domain_id = :domainId
            WHERE c.id = cd.category_id
                AND cd.domain_id = :domainId
                AND c.level = :level
            ',
            [
                'domainId' => $domainConfig->getId(),
                'locale' => $domainConfig->getLocale(),
                'level' => $level,
            ],
        );
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->categoryVisibilityRecalculationScheduler->isRecalculationScheduled()) {
            return;
        }

        try {
            $this->em->beginTransaction();
            $this->refreshCategoriesVisibility();
            $this->em->commit();
        } catch (Exception $ex) {
            $this->em->rollback();
            throw $ex;
        }
    }
}
