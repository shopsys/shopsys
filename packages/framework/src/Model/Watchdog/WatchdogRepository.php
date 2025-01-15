<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Watchdog;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Model\Stock\ProductStock;
use Shopsys\FrameworkBundle\Model\Stock\Stock;
use Shopsys\FrameworkBundle\Model\Stock\StockDomain;
use Shopsys\FrameworkBundle\Model\Watchdog\Exception\WatchdogNotFoundException;

class WatchdogRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\String\TransformStringHelper $transformStringHelper
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ProductRepository $productRepository,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly Domain $domain,
        protected readonly TransformStringHelper $transformStringHelper,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getWatchdogRepository(): EntityRepository
    {
        return $this->em->getRepository(Watchdog::class);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog
     */
    public function getById(int $id): Watchdog
    {
        $watchdog = $this->getWatchdogRepository()->find($id);

        if ($watchdog === null) {
            throw new WatchdogNotFoundException($id);
        }

        return $watchdog;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('w')
            ->from(Watchdog::class, 'w');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param string $email
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog|null
     */
    public function findByProductEmailAndDomainId(Product $product, string $email, int $domainId): ?Watchdog
    {
        return $this->getQueryBuilder()
            ->where('w.product = :product')
            ->andWhere('w.email = :email')
            ->andWhere('w.domainId = :domainId')
            ->setParameter('product', $product)
            ->setParameter('email', $email)
            ->setParameter('domainId', $domainId)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param string $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getWatchdogProductsQueryBuilder(string $locale): QueryBuilder
    {
        return $this->getQueryBuilder()
            ->select('IDENTITY(w.product) as productId')
            ->addSelect('pt.name as productName')
            ->addSelect('p.catnum as productCatnum')
            ->addSelect('COUNT(w.product) as watchdogCount')
            ->join('w.product', 'p')
            ->join('p.translations', 'pt', Join::WITH, 'pt.locale = :locale')
            ->setParameter('locale', $locale)
            ->groupBy('w.product, pt.name, p.catnum');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getWatchdogsByProductQueryBuilder(Product $product): QueryBuilder
    {
        return $this->getQueryBuilder()
            ->where('w.product = :product')
            ->setParameter('product', $product);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog|null
     */
    public function findNextWatchdogToSend(): ?Watchdog
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId);
            $queryBuilder = $this->productRepository->getAllSellableWithoutInquiriesQueryBuilder(
                $domainId,
                $pricingGroup,
            );
            $queryBuilder
                ->select('w')
                ->join(Watchdog::class, 'w', Join::WITH, 'w.product = p')
                ->join(ProductStock::class, 'ps', Join::WITH, 'ps.product = p')
                ->join(Stock::class, 's', Join::WITH, 'ps.stock = s')
                ->join(StockDomain::class, 'sd', Join::WITH, 's.id = sd.stock AND sd.domainId = :domainId AND sd.isEnabled = TRUE')
                ->andWhere('sd.domainId = w.domainId')
                ->groupBy('w.id')
                ->having('SUM(ps.productQuantity) > 0')
                ->orderBy('w.createdAt', 'DESC')
                ->setMaxResults(1);
            $result = $queryBuilder->getQuery()->getOneOrNullResult();

            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    /**
     * @param string $email
     */
    public function deleteByEmail(string $email): void
    {
        if ($this->transformStringHelper->emptyToNull($email) === null) {
            return;
        }

        $this->em->createQueryBuilder()
            ->delete(Watchdog::class, 'w')
            ->where('w.email = :email')
            ->setParameter('email', $email)
            ->getQuery()->execute();
    }

    /**
     * @param string $email
     * @return \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog[]
     */
    public function getWatchdogsByEmail(string $email): array
    {
        return $this->getWatchdogRepository()->findBy(['email' => $email]);
    }
}
