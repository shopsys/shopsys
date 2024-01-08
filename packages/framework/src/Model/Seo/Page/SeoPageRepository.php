<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo\Page;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Seo\Page\Exception\SeoPageNotFoundException;

class SeoPageRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param int $seoPageId
     * @return \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage
     */
    public function getById(int $seoPageId): SeoPage
    {
        /** @var \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage|null $seoPage */
        $seoPage = $this->getSeoPageRepository()->find($seoPageId);

        if ($seoPage === null) {
            $message = sprintf('SeoPage with ID %d not found.', $seoPageId);

            throw new SeoPageNotFoundException($message);
        }

        return $seoPage;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage[]
     */
    public function getAll(): array
    {
        return $this->getSeoPageRepository()->findAll();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllQueryBuilder(): QueryBuilder
    {
        return $this->getSeoPageRepository()->createQueryBuilder('sp');
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getSeoPageRepository(): EntityRepository
    {
        return $this->em->getRepository(SeoPage::class);
    }
}
