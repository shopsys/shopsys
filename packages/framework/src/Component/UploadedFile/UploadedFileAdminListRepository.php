<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;

class UploadedFileAdminListRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @param string $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getUploadedFileListQueryBuilder(string $locale): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->from(UploadedFile::class, 'u')
            ->leftJoin('u.translations', 'ut', Join::WITH, 'ut.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('u.id', 'DESC')
            ->select('u, ut');
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $quickSearchData
     */
    public function extendQueryBuilderByQuickSearchData(
        QueryBuilder $queryBuilder,
        QuickSearchFormData $quickSearchData,
    ): void {
        if ($quickSearchData->text !== null && $quickSearchData->text !== '') {
            $querySearchText = DatabaseSearching::getFullTextLikeSearchString($quickSearchData->text);

            $queryBuilder
                ->leftJoin('u.translations', 'ut_all')
                ->distinct()
                ->andWhere($queryBuilder->expr()->orX(
                    'NORMALIZED(ut_all.name) LIKE NORMALIZED(:text)',
                    'NORMALIZED(u.name) LIKE NORMALIZED(:text)',
                    'NORMALIZED(u.extension) LIKE NORMALIZED(:text)',
                    'NORMALIZED(CONCAT(u.name, \'.\', u.extension)) LIKE NORMALIZED(:text)',
                ))
                ->setParameter('text', $querySearchText);
        }
    }
}
