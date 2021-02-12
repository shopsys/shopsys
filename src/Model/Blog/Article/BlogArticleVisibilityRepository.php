<?php

declare(strict_types=1);

namespace App\Model\Blog\Article;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class BlogArticleVisibilityRepository
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

    public function refreshArticlesVisibility(): void
    {
        try {
            $this->em->beginTransaction();

            $domains = $this->domain->getAll();
            foreach ($domains as $domainConfig) {
                $this->refreshBlogArticlesVisibilityOnDomain($domainConfig);
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
    private function refreshBlogArticlesVisibilityOnDomain(DomainConfig $domainConfig): void
    {
        $query = $this->em->createNativeQuery(
            'UPDATE blog_article_domains AS bad
                SET visible = CASE
                    WHEN (
                        ba.hidden = FALSE
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
            new ResultSetMapping()
        );

        $query->execute([
            'locale' => $domainConfig->getLocale(),
            'domainId' => $domainConfig->getId(),
        ]);
    }
}
