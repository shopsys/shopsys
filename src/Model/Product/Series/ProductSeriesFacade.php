<?php

declare(strict_types=1);

namespace App\Model\Product\Series;

use App\Model\Product\Series\Category\ProductSeriesCategory;
use App\Model\Product\Series\Exception\MissingBaseFriendlyUrlForDomainException;
use App\Model\Product\Series\Exception\ProductSeriesNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

class ProductSeriesFacade implements ProductSeriesFacadeInterface
{
    public const BASE_FRIENDY_URL_BY_DOMAIN_ID = [
        1 => 'nabytkove-programy',
        2 => 'nabytkove-programy', //same as in czech language :-)
    ];

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

        $this->friendlyUrlFacade->saveUrlListFormData('front_productseries_detail', $productSeries->getId(), $productSeriesData->url);

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
        $productSeries = $this->productSeriesRepository->getById($id);

        $productSeries->edit($productSeriesData);
        $this->em->persist($productSeries);

        $this->imageFacade->manageImages($productSeries, $productSeriesData->images);

        $this->friendlyUrlFacade->saveUrlListFormData('front_productseries_detail', $productSeries->getId(), $productSeriesData->url);

        $this->storeUrls($productSeries);

        $this->em->flush();

        return $productSeries;
    }

    /**
     * @return int[]
     */
    public function getAllAkeneoProductSeriesIds(): array
    {
        return $this->productSeriesRepository->findProductSeriesIdsWithAkeneoCode();
    }

    /**
     * @param \App\Model\Product\Series\ProductSeries $productSeries
     */
    public function delete(ProductSeries $productSeries): void
    {
        $this->em->remove($productSeries);
        $this->em->flush();
    }

    /**
     * @param \App\Model\Product\Series\ProductSeries $productSeries
     */
    private function storeUrls(ProductSeries $productSeries): void
    {
        $domains = $this->domain->getAll();
        foreach ($domains as $domain) {

            /** @var \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade */
            $friendlyUrlFacade = $this->friendlyUrlFacade;
            $friendlyUrlFacade->createFriendlyUrlForDomain(
                'front_productseries_detail',
                $productSeries->getId(),
                $productSeries->getName($domain->getLocale()),
                $domain->getId(),
                [$this->getBaseFriendlyUrlForDomain($domain)]
            );
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domain
     * @throws \App\Model\Product\Series\Exception\MissingBaseFriendlyUrlForDomainException
     * @return string
     */
    private function getBaseFriendlyUrlForDomain(DomainConfig $domain)
    {
        if (!array_key_exists($domain->getId(), self::BASE_FRIENDY_URL_BY_DOMAIN_ID)) {
            throw new MissingBaseFriendlyUrlForDomainException($domain->getName());
        }
        return self::BASE_FRIENDY_URL_BY_DOMAIN_ID[$domain->getId()];
    }

    /**
     * @param int $id
     * @return \App\Model\Product\Series\ProductSeries
     */
    public function getById(int $id): ProductSeries
    {
        return $this->productSeriesRepository->getById($id);
    }

    /**
     * @param int $id
     * @param int $domainId
     * @return \App\Model\Product\Series\ProductSeries
     */
    public function getVisibleProductSeriesByIdAndDomainId(int $id, int $domainId): ProductSeries
    {
        $productSeries = $this->productSeriesRepository->findVisibleProductSeriesByIdAndDomainId($id, $domainId);
        if ($productSeries === null) {
            throw new ProductSeriesNotFoundException();
        }
        return $productSeries;
    }

    /**
     * @param int $domainId
     * @return \App\Model\Product\Series\ProductSeries[]
     */
    public function getAllVisibleProductSeriesByDomainId(int $domainId): array
    {
        return $this->productSeriesRepository->getAllVisibleProductSeriesByDomainId($domainId);
    }

    /**
     * @param \App\Model\Product\Series\Category\ProductSeriesCategory $productSeriesCategory
     * @return \App\Model\Product\Series\ProductSeries[]
     */
    public function getByProductSeriesCategoryForCurrentDomain(ProductSeriesCategory $productSeriesCategory): array
    {
        return $this->productSeriesRepository->getByProductSeriesCategoryAndDomainId($productSeriesCategory, $this->domain->getId());
    }

    /**
     * @param string $akeneoCode
     * @return \App\Model\Product\Series\ProductSeries|null
     */
    public function findByAkeneoCode(string $akeneoCode): ?ProductSeries
    {
        return $this->productSeriesRepository->findByAkeneoCode($akeneoCode);
    }

    /**
     * @return string[]
     */
    public function findProductSeriesCodesWithAkeneoCode(): array
    {
        return $this->productSeriesRepository->findProductSeriesCodesWithAkeneoCode();
    }
}
