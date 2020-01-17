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


    public function __construct(
        EntityManagerInterface $em,
        ProductSeriesFactoryInterface $productSeriesFactory,
        ProductSeriesRepository $productSeriesRepository,
        FriendlyUrlFacade $friendlyUrlFacade,
        Domain $domain,
        ImageFacade $imageFacade
    )
    {
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
        $domains = $this->domain->getAll();
        foreach ($domains as $domain) {
            $this->friendlyUrlFacade->createFriendlyUrlForDomain(
                'front_productseries_detail',
                $productSeries->getId(),
                $productSeries->getName($domain->getLocale()),
                $domain->getId()
            );
        }
        return $productSeries;
    }

    public function edit(int $id, ProductSeriesData $productSeriesData): ProductSeries
    {
        $productSeries = $this->productSeriesRepository->findById($id);
        $productSeries->edit($productSeriesData);
        $this->em->persist($productSeries);

        $this->imageFacade->manageImages($productSeries, $productSeriesData->images);

        $this->friendlyUrlFacade->saveUrlListFormData('front_productseries_detail', $productSeries->getId(), $productSeriesData->urls);
        $domains = $this->domain->getAll();
        foreach ($domains as $domain) {
            $this->friendlyUrlFacade->createFriendlyUrlForDomain(
                'front_productseries_detail',
                $productSeries->getId(),
                $productSeries->getName($domain->getLocale()),
                $domain->getId()
            );
        }

        $this->em->flush();
        return $productSeries;
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


}
