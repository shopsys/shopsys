<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class BlogArticleVisibilityRepository
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected Domain $domain,
    ) {
    }

    public function refreshArticlesVisibility(): void
    {
        try {
            $this->em->beginTransaction();

            $domains = $this->domain->getAll();

            foreach ($domains as $domainConfig) {
                $this->refreshBlogArticlesVisibilityOnDomain($domainConfig);
            }

            $this->em->commit();
        } catch (Exception $ex) {
            $this->em->rollback();

            throw $ex;
        }
    }

    protected function refreshBlogArticlesVisibilityOnDomain(DomainConfig $domainConfig): void
    {
        $this->em->getConnection()->executeStatement(
            'UPDATE blog_article_domains AS bad
                SET visible = CASE
                    WHEN (
                        bad.status IN (:statusPublished, :statusPreview)
                        AND EXISTS (
                            SELECT 1
                            FROM blog_article_translations AS bat
                            WHERE bat.translatable_id = bad.blog_article_id
                                AND bat.locale = :locale
                                AND bat.name IS NOT NULL
                        )
                        AND EXISTS (
                            SELECT 1
                            FROM blog_article_blog_category_domains AS babcd
                            JOIN blog_category_domains AS bcd ON bcd.blog_category_id = babcd.blog_category_id
                                AND bcd.domain_id = babcd.domain_id
                            WHERE babcd.blog_article_id = ba.id
                                AND babcd.domain_id = bad.domain_id
                                AND bcd.visible = TRUE
                        )
                    )
                    THEN TRUE
                    ELSE FALSE
                END
            FROM blog_articles AS ba
            WHERE ba.id = bad.blog_article_id
                AND bad.domain_id = :domainId',
            [
                'locale' => $domainConfig->getLocale(),
                'domainId' => $domainConfig->getId(),
                'statusPublished' => BlogArticleStatusEnum::STATUS_PUBLISHED,
                'statusPreview' => BlogArticleStatusEnum::STATUS_PREVIEW,
            ],
            [
                'locale' => Types::STRING,
                'domainId' => Types::INTEGER,
                'statusPublished' => Types::STRING,
                'statusPreview' => Types::STRING,
            ],
        );
    }
}
