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
    /**
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\WatchdogRepository $watchdogRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\WatchdogFactory $watchdogFactory
     * @param \Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper $databaseSearchingHelper
     */
    public function __construct(
        protected readonly WatchdogRepository $watchdogRepository,
        protected readonly EntityManagerInterface $em,
        protected readonly WatchdogFactory $watchdogFactory,
        protected readonly DatabaseSearchingHelper $databaseSearchingHelper,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\WatchdogData $watchdogData
     * @return \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog
     */
    public function create(WatchdogData $watchdogData): Watchdog
    {
        $watchdog = $this->watchdogFactory->create($watchdogData);

        $this->em->persist($watchdog);
        $this->em->flush();

        return $watchdog;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog $watchdog
     * @return \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog
     */
    public function updateValidity(Watchdog $watchdog): Watchdog
    {
        $watchdog->updateValidity();

        $this->em->flush();

        return $watchdog;
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog
     */
    public function getById(int $id): Watchdog
    {
        return $this->watchdogRepository->getById($id);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param string $email
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog|null
     */
    public function findByProductEmailAndDomainId(Product $product, string $email, int $domainId): ?Watchdog
    {
        return $this->watchdogRepository->findByProductEmailAndDomainId($product, $email, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $quickSearchData
     * @param string $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $quickSearchData
     * @return \Doctrine\ORM\QueryBuilder
     */
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

    /**
     * @param int $id
     */
    public function deleteById(int $id): void
    {
        $watchdog = $this->getById($id);

        $this->em->remove($watchdog);
        $this->em->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog|null
     */
    public function findNextWatchdogToSend(): ?Watchdog
    {
        return $this->watchdogRepository->findNextWatchdogToSend();
    }

    /**
     * @param string $email
     */
    public function deleteByEmail(string $email): void
    {
        $this->watchdogRepository->deleteByEmail($email);
    }

    /**
     * @param string $email
     * @return \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog[]
     */
    public function getWatchdogsByEmail(string $email): array
    {
        return $this->watchdogRepository->getWatchdogsByEmail($email);
    }
}
