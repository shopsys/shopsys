<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Media;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Model\Media\Exception\MediaNotFoundException;
use SortDirection;

class MediaRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly DatabaseSearchingHelper $databaseSearchingHelper,
    ) {
    }

    public function getById(int $mediaId): Media
    {
        $media = $this->em->find(Media::class, $mediaId);

        if ($media === null) {
            throw new MediaNotFoundException('Media with ID ' . $mediaId . ' not found');
        }

        return $media;
    }

    public function getPaginatedByTitle(string $search, int $page, int $limit): PaginationResult
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('m')
            ->from(Media::class, 'm')
            ->orderBy('m.createdAt', SortDirection::Descending)
            ->addOrderBy('m.id', SortDirection::Descending);

        if ($search !== '') {
            $queryBuilder
                ->andWhere('NORMALIZED(m.title) LIKE NORMALIZED(:search)')
                ->setParameter('search', $this->databaseSearchingHelper->getFullTextLikeSearchString($search));
        }

        return (new QueryPaginator($queryBuilder))->getResult($page, $limit);
    }
}
