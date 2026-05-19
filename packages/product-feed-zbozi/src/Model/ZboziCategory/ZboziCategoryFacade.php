<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Category\CategoryEvent;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ZboziCategoryFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ZboziCategoryRepository $zboziCategoryRepository,
        protected readonly CategoryRepository $categoryRepository,
        protected readonly ZboziCategoryFactory $zboziCategoryFactory,
        protected readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryData[] $newZboziCategoriesData
     */
    public function saveZboziCategories(array $newZboziCategoriesData, string $locale): void
    {
        $existingZboziCategories = $this->zboziCategoryRepository->getAllIndexedByZboziId($locale);

        $this->removeOldZboziCategories($newZboziCategoriesData, $existingZboziCategories);

        foreach ($newZboziCategoriesData as $newZboziCategoryData) {
            if (!array_key_exists($newZboziCategoryData->zboziId, $existingZboziCategories)) {
                $newZboziCategory = $this->zboziCategoryFactory->create($newZboziCategoryData);
                $this->em->persist($newZboziCategory);
            } else {
                $existingZboziCategory = $existingZboziCategories[$newZboziCategoryData->zboziId];

                if ($existingZboziCategory->getFullName() !== $newZboziCategoryData->fullName) {
                    foreach ($existingZboziCategory->getCategories() as $category) {
                        $this->eventDispatcher->dispatch(new CategoryEvent($category), CategoryEvent::UPDATE);
                    }
                }

                $newZboziCategoryData->categories = $existingZboziCategory->getCategories();
                $existingZboziCategory->edit($newZboziCategoryData);
            }
        }

        $this->em->flush();
    }

    /**
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryData[] $newZboziCategoriesData
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategory[] $existingZboziCategoriesIndexedByIds
     */
    protected function removeOldZboziCategories(
        array $newZboziCategoriesData,
        array $existingZboziCategoriesIndexedByIds,
    ): void {
        $existingZboziCategoriesIds = array_keys($existingZboziCategoriesIndexedByIds);

        $newZboziCategoriesIds = [];

        foreach ($newZboziCategoriesData as $newZboziCategoryData) {
            $newZboziCategoriesIds[] = $newZboziCategoryData->zboziId;
        }

        $zboziCategoryIdsToDelete = array_diff($existingZboziCategoriesIds, $newZboziCategoriesIds);

        foreach ($zboziCategoryIdsToDelete as $zboziCategoryIdToDelete) {
            $this->em->remove($existingZboziCategoriesIndexedByIds[$zboziCategoryIdToDelete]);
        }
    }

    public function changeZboziCategoryForCategoryId(
        int $categoryId,
        ZboziCategory $zboziCategory,
        string $locale,
    ): void {
        $oldZboziCategoryByCategoryId = $this->zboziCategoryRepository->findByCategoryIdAndLocale($categoryId, $locale);

        $category = $this->categoryRepository->getById($categoryId);

        if ($oldZboziCategoryByCategoryId === null) {
            $zboziCategory->addCategory($category);
            $this->eventDispatcher->dispatch(new CategoryEvent($category), CategoryEvent::UPDATE);
        } elseif ($oldZboziCategoryByCategoryId->getZboziId() !== $zboziCategory->getZboziId()) {
            $oldZboziCategoryByCategoryId->removeCategory($category);
            $zboziCategory->addCategory($category);
            $this->eventDispatcher->dispatch(new CategoryEvent($category), CategoryEvent::UPDATE);
        }

        $this->em->flush();
    }

    public function findByCategoryIdAndLocale(int $categoryId, string $locale): ?ZboziCategory
    {
        return $this->zboziCategoryRepository->findByCategoryIdAndLocale($categoryId, $locale);
    }

    public function findFullNameByCategoryIdAndLocale(int $categoryId, string $locale): ?string
    {
        return $this->findByCategoryIdAndLocale($categoryId, $locale)?->getFullName();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @return array<int, string>
     */
    public function getFullNamesByProductsIndexedByProductId(array $products, DomainConfig $domainConfig): array
    {
        $productIds = array_map(
            static fn (Product $product) => $product->getId(),
            $products,
        );

        $mainCategoryIdsByProductId = $this->categoryRepository->getProductMainCategoryIdsIndexedByProductId(
            $productIds,
            $domainConfig->getId(),
        );

        if ($mainCategoryIdsByProductId === []) {
            return [];
        }

        $fullNamesByCategoryId = $this->zboziCategoryRepository->getFullNamesByCategoryIdsIndexedByCategoryId(
            array_values(array_unique($mainCategoryIdsByProductId)),
            $domainConfig->getLocale(),
        );

        $fullNamesByProductId = [];

        foreach ($mainCategoryIdsByProductId as $productId => $categoryId) {
            if (array_key_exists($categoryId, $fullNamesByCategoryId)) {
                $fullNamesByProductId[$productId] = $fullNamesByCategoryId[$categoryId];
            }
        }

        return $fullNamesByProductId;
    }

    public function removeZboziCategoryForCategoryId(int $categoryId, string $locale): void
    {
        $zboziCategory = $this->zboziCategoryRepository->findByCategoryIdAndLocale($categoryId, $locale);

        if ($zboziCategory === null) {
            return;
        }

        $category = $this->categoryRepository->getById($categoryId);
        $zboziCategory->removeCategory($category);

        $this->em->flush();

        $this->eventDispatcher->dispatch(new CategoryEvent($category), CategoryEvent::UPDATE);
    }

    public function getOneByZboziIdAndLocale(int $zboziId, string $locale): ZboziCategory
    {
        return $this->zboziCategoryRepository->getOneByZboziIdAndLocale($zboziId, $locale);
    }

    /**
     * @return \Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategory[]
     */
    public function getAllIndexedByZboziId(string $locale): array
    {
        return $this->zboziCategoryRepository->getAllIndexedByZboziId($locale);
    }
}
