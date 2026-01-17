<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Watchdog;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Product\Product;

class WatchdogFacade
{
    public function __construct(
        protected readonly WatchdogRepository $watchdogRepository,
        protected readonly EntityManagerInterface $em,
        protected readonly WatchdogFactory $watchdogFactory,
        protected readonly DatabaseSearchingHelper $databaseSearchingHelper,
    ) {
    }

    public function create(WatchdogData $watchdogData): Watchdog
    {
        $watchdog = $this->watchdogFactory->create($watchdogData);

        $this->em->persist($watchdog);
        $this->em->flush();

        return $watchdog;
    }

    public function updateValidity(Watchdog $watchdog): Watchdog
    {
        $watchdog->updateValidity();

        $this->em->flush();

        return $watchdog;
    }

    public function getById(int $id): Watchdog
    {
        return $this->watchdogRepository->getById($id);
    }

    public function findByProductEmailAndDomainId(Product $product, string $email, int $domainId): ?Watchdog
    {
        return $this->watchdogRepository->findByProductEmailAndDomainId($product, $email, $domainId);
    }

    public function getWatchdogProductListQueryBuilderByQuickSearchData(
        QuickSearchFormData $quickSearchData,
        string $locale,
    ): QueryBuilder {
        $queryBuilder = $this->watchdogRepository->getWatchdogProductsQueryBuilder($locale);

        if ($quickSearchData->text !== null && $quickSearchData->text !== '') {
            $queryBuilder
                ->andWhere('(
                    NORMALIZED(p.catnum) LIKE NORMALIZED(:text)
                    OR
                    NORMALIZED(pt.name) LIKE NORMALIZED(:text)
                )');
            $querySearchText = $this->databaseSearchingHelper->getFullTextLikeSearchString($quickSearchData->text);
            $queryBuilder->setParameter('text', $querySearchText);
        }

        return $queryBuilder;
    }

    public function getWatchdogsByProductQueryBuilderByQuickSearchData(
        Product $product,
        QuickSearchFormData $quickSearchData,
    ): QueryBuilder {
        $queryBuilder = $this->watchdogRepository->getWatchdogsByProductQueryBuilder($product);

        if ($quickSearchData->text !== null && $quickSearchData->text !== '') {
            $queryBuilder
                ->andWhere('(
                    NORMALIZED(w.email) LIKE NORMALIZED(:text)
                )');
            $querySearchText = $this->databaseSearchingHelper->getFullTextLikeSearchString($quickSearchData->text);
            $queryBuilder->setParameter('text', $querySearchText);
        }

        return $queryBuilder;
    }

    public function deleteById(int $id): void
    {
        $watchdog = $this->getById($id);

        $this->em->remove($watchdog);
        $this->em->flush();
    }

    public function findNextWatchdogToSend(): ?Watchdog
    {
        return $this->watchdogRepository->findNextWatchdogToSend();
    }

    public function deleteByEmail(string $email): void
    {
        $this->watchdogRepository->deleteByEmail($email);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog[]
     */
    public function getWatchdogsByEmail(string $email): array
    {
        return $this->watchdogRepository->getWatchdogsByEmail($email);
    }
}
