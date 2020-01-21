<?php

declare(strict_types=1);

namespace App\Model\Product\Series;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

class ProductSeriesFacade implements ProductSeriesFacadeInterface
{
    /**
     * @var \App\Model\Product\Series\ProductSeriesFactoryInterface
     */
    private $productSeriesFactory;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \App\Model\Product\Series\ProductSeriesRepository
     */
    private $productSeriesRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private $friendlyUrlFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    private $imageFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Product\Series\ProductSeriesFactoryInterface $productSeriesFactory
     * @param \App\Model\Product\Series\ProductSeriesRepository $productSeriesRepository
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        ProductSeriesFactoryInterface $productSeriesFactory,
        ProductSeriesRepository $productSeriesRepository,
        FriendlyUrlFacade $friendlyUrlFacade,
        Domain $domain,
        ImageFacade $imageFacade
    ) {
        $this->productSeriesFactory = $productSeriesFactory;
        $this->em = $em;
        $this->productSeriesRepository = $productSeriesRepository;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->domain = $domain;
        $this->imageFacade = $imageFacade;
    }

    /**
     * @param \App\Model\Product\Series\ProductSeriesData $productSeriesData
     * @return \App\Model\Product\Series\ProductSeries
     */
    public function create(ProductSeriesData $productSeriesData): ProductSeries
    {
        $productSeries = $this->productSeriesFactory->create($productSeriesData);
        $this->em->persist($productSeries);
        $this->em->flush();

        $this->imageFacade->manageImages($productSeries, $productSeriesData->images);

        $this->friendlyUrlFacade->saveUrlListFormData('front_productseries_detail', $productSeries->getId(), $productSeriesData->urls);

        $this->storeUrls($productSeries);

        return $productSeries;
    }

    /**
     * @param int $id
     * @param \App\Model\Product\Series\ProductSeriesData $productSeriesData
     * @return \App\Model\Product\Series\ProductSeries
     */
    public function edit(int $id, ProductSeriesData $productSeriesData): ProductSeries
    {
        $productSeries = $this->productSeriesRepository->findById($id);
        $productSeries->edit($productSeriesData);
        $this->em->persist($productSeries);

        $this->imageFacade->manageImages($productSeries, $productSeriesData->images);

        $this->friendlyUrlFacade->saveUrlListFormData('front_productseries_detail', $productSeries->getId(), $productSeriesData->urls);

        $this->storeUrls($productSeries);

        $this->em->flush();
        return $productSeries;
    }

    /**
     * @param int $id
     */
    public function delete(int $id): void
    {
        $productSeries = $this->productSeriesRepository->findById($id);
        $this->em->remove($productSeries);
        $this->em->flush();
    }

    /**
     * @param \App\Model\Product\Series\ProductSeries $productSeries
     */
    protected function storeUrls(ProductSeries $productSeries): void
    {
        $domains = $this->domain->getAll();
        foreach ($domains as $domain) {
            $this->friendlyUrlFacade->createFriendlyUrlForDomain(
                'front_productseries_detail',
                $productSeries->getId(),
                $productSeries->getName($domain->getLocale()),
                $domain->getId(),
                [ProductSeries::BASE_FRIENDY_URL_CZ]
            );
        }
    }

    /**
     * @param int $id
     * @return \App\Model\Product\Series\ProductSeries
     */
    public function getById(int $id): ProductSeries
    {
        return $this->productSeriesRepository->findById($id);
    }

    /**
     * @param int $id
     * @param int $domainId
     * @return \App\Model\Product\Series\ProductSeries
     */
    public function getVisibleProductSeriesById(int $id, int $domainId): ProductSeries
    {
        return $this->productSeriesRepository->findVisibleProductSeriesById($id, $domainId);
    }

    /**
     * @return array
     */
    public function getAllVisibleProductSeriesByDomain(): array
    {
        return $this->productSeriesRepository->getAllVisibleProductSeriesByDomain($this->domain->getId());
    }
}
