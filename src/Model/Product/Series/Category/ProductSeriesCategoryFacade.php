<?php

declare(strict_types=1);


namespace App\Model\Product\Series\Category;


use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\Model\Product\Series\Exception\MissingBaseFriendlyUrlForDomainException;
use App\Model\Product\Series\ProductSeriesFacade;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class ProductSeriesCategoryFacade
{

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;
    /**
     * @var \App\Model\Product\Series\Category\ProductSeriesCategoryRepository
     */
    private $productSeriesCategoryRepository;
    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private $friendlyUrlFacade;
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    public function __construct(
        EntityManagerInterface $em,
        ProductSeriesCategoryRepository $productSeriesCategoryRepository,
        FriendlyUrlFacade $friendlyUrlFacade,
        Domain $domain
    )
    {
        $this->em = $em;
        $this->productSeriesCategoryRepository = $productSeriesCategoryRepository;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->domain = $domain;
    }

    /**
     * @param \App\Model\Product\Series\Category\ProductSeriesCategoryData $productSeriesCategoryData
     * @return \App\Model\Product\Series\Category\ProductSeriesCategory
     */
    public function create(ProductSeriesCategoryData $productSeriesCategoryData): ProductSeriesCategory
    {
        $productSeriesCategory = new ProductSeriesCategory($productSeriesCategoryData);
        $this->em->persist($productSeriesCategory);
        $this->em->flush();

        $this->friendlyUrlFacade->saveUrlListFormData('front_productseriescategory_detail', $productSeriesCategory->getId(), $productSeriesCategoryData->url);

        $this->storeUrls($productSeriesCategory);

        return $productSeriesCategory;
    }

    /**
     * @param int $productSeriesCategoryId
     * @param \App\Model\Product\Series\Category\ProductSeriesCategoryData $productSeriesCategoryData
     * @return \App\Model\Product\Series\Category\ProductSeriesCategory
     */
    public function edit(int $productSeriesCategoryId, ProductSeriesCategoryData $productSeriesCategoryData): ProductSeriesCategory
    {
        $productSeriesCategory = $this->productSeriesCategoryRepository->getById($productSeriesCategoryId);
        $productSeriesCategory->edit($productSeriesCategoryData);
        $this->em->flush();

        $this->friendlyUrlFacade->saveUrlListFormData('front_productseriescategory_detail', $productSeriesCategory->getId(), $productSeriesCategoryData->url);

        $this->storeUrls($productSeriesCategory);

        return $productSeriesCategory;
    }

    /**
     * @param \App\Model\Product\Series\Category\ProductSeriesCategory $productSeriesCategory
     */
    public function delete(ProductSeriesCategory $productSeriesCategory): void
    {
        $this->em->remove($productSeriesCategory);
        $this->em->flush();
    }

    /**
     * @param int $productSeriesCategoryId
     * @return \App\Model\Product\Series\Category\ProductSeriesCategory
     */
    public function getById(int $productSeriesCategoryId): ProductSeriesCategory
    {
        return $this->productSeriesCategoryRepository->getById($productSeriesCategoryId);
    }

    /**
     * @param \App\Model\Product\Series\Category\ProductSeriesCategory $productSeriesCategory
     */
    private function storeUrls(ProductSeriesCategory $productSeriesCategory): void
    {
        $domains = $this->domain->getAll();
        foreach ($domains as $domain) {

            /** @var \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade */
            $friendlyUrlFacade = $this->friendlyUrlFacade;
            $friendlyUrlFacade->createFriendlyUrlForDomain(
                'front_productseries_detail',
                $productSeriesCategory->getId(),
                $productSeriesCategory->getName($domain->getLocale()),
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
        if (!array_key_exists($domain->getId(), ProductSeriesFacade::BASE_FRIENDY_URL_BY_DOMAIN_ID)) {
            throw new MissingBaseFriendlyUrlForDomainException($domain->getName());
        }
        return ProductSeriesFacade::BASE_FRIENDY_URL_BY_DOMAIN_ID[$domain->getId()];
    }

    /**
     * @param int $productSeriesId
     * @return array
     */
    public function getProductSeriesCategoriesByProductSeriesId(int $productSeriesId): array
    {
        return [];
    }
}