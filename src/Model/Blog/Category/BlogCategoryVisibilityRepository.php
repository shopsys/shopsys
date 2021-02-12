<?php

declare(strict_types=1);

namespace App\Model\Blog\Category;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class BlogCategoryVisibilityRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        EntityManagerInterface $em,
        Domain $domain
    ) {
        $this->em = $em;
        $this->domain = $domain;
    }

    public function refreshCategoriesVisibility(): void
    {
        try {
            $this->em->beginTransaction();

            $domains = $this->domain->getAll();
            foreach ($domains as $domainConfig) {
                $this->refreshBlogCategoriesVisibilityOnDomain($domainConfig);
            }

            $this->em->commit();
        } catch (\Exception $ex) {
            $this->em->rollback();
            throw $ex;
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     */
    private function refreshBlogCategoriesVisibilityOnDomain(DomainConfig $domainConfig): void
    {
        $this->setRootBlogCategoryVisibleOnDomain($domainConfig);

        $maxLevel = $this->getMaxLevelOnDomain($domainConfig);

        for ($level = 1; $level <= $maxLevel; $level++) {
            $this->refreshBlogCategoriesVisibilityOnDomainAndLevel($domainConfig, $level);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     */
    private function setRootBlogCategoryVisibleOnDomain(DomainConfig $domainConfig): void
    {
        $this->em->getConnection()->executeUpdate(
            'UPDATE blog_category_domains AS bcd
                SET visible = TRUE

            FROM blog_categories AS bc
            WHERE bc.id = bcd.blog_category_id
                AND bcd.domain_id = :domainId
                AND bc.parent_id IS NULL
            ',
            [
                'domainId' => $domainConfig->getId(),
            ]
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return int
     */
    private function getMaxLevelOnDomain(DomainConfig $domainConfig): int
    {
        return (int)$this->em->getConnection()->fetchColumn(
            'SELECT MAX(bc.level)
            FROM blog_categories bc
            JOIN blog_category_domains bcd ON bcd.blog_category_id = bc.id AND bcd.domain_id = :domainId
            ',
            [
                'domainId' => $domainConfig->getId(),
            ]
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int $level
     */
    private function refreshBlogCategoriesVisibilityOnDomainAndLevel(DomainConfig $domainConfig, int $level): void
    {
        $this->em->getConnection()->executeUpdate(
            'UPDATE blog_category_domains AS bcd
                SET visible = (
                    bcd.enabled = TRUE
                    AND
                    bct.name IS NOT NULL
                    AND
                    parent_bcd.visible = TRUE
                )

            FROM blog_categories AS bc
            LEFT JOIN blog_category_translations bct ON bct.translatable_id = bc.id AND bct.locale = :locale
            JOIN blog_category_domains AS parent_bcd ON parent_bcd.blog_category_id = bc.parent_id AND parent_bcd.domain_id = :domainId
            WHERE bc.id = bcd.blog_category_id
                AND bcd.domain_id = :domainId
                AND bc.level = :level
            ',
            [
                'domainId' => $domainConfig->getId(),
                'locale' => $domainConfig->getLocale(),
                'level' => $level,
            ]
        );
    }
}
