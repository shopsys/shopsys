<?php

declare(strict_types=1);

namespace App\Model\Product\Comparison;

use App\Model\Customer\User\CustomerUser;
use App\Model\Product\Comparison\Exception\ComparedItemAlreadyExistsException;
use App\Model\Product\Comparison\Exception\ComparisonNotFoundException;
use App\Model\Product\Comparison\Exception\HandlingWithOtherLoggedCustomerComparisonException;
use App\Model\Product\Comparison\Item\ComparedItem;
use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;

class ComparisonFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Product\Comparison\ComparisonRepository $comparisonRepository
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ComparisonRepository $comparisonRepository
    ) {
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @param string|null $comparisonUuid
     * @return \App\Model\Product\Comparison\Comparison
     */
    public function addProductToComparison(Product $product, ?CustomerUser $customerUser, ?string $comparisonUuid): Comparison
    {
        if ($customerUser === null) {
            $comparison = $this->getOrCreateComparisonByUuid($comparisonUuid);
            if ($comparison->getCustomerUser() !== null) {
                throw new HandlingWithOtherLoggedCustomerComparisonException('Handling with different customer comparison.');
            }
        } else {
            $comparison = $this->getOrCreateComparisonOfCustomerUser($customerUser);
        }

        if ($comparison->isProductInComparison($product)) {
            throw new ComparedItemAlreadyExistsException(sprintf('Product %s in comparison already exists.', $product->getName()));
        }

        $newComparedItem = new ComparedItem($comparison, $product);
        $this->em->persist($newComparedItem);

        $comparison->addItem($newComparedItem);
        $this->em->flush();

        return $comparison;
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return \App\Model\Product\Comparison\Comparison
     */
    public function getOrCreateComparisonOfCustomerUser(CustomerUser $customerUser): Comparison
    {
        $comparison = $this->comparisonRepository->findByCustomerUser($customerUser);

        if ($comparison === null) {
            $comparison = new Comparison($customerUser);
            $this->em->persist($comparison);
            $this->em->flush();
        }

        return $comparison;
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return \App\Model\Product\Comparison\Comparison
     */
    public function getComparisonOfCustomerUser(CustomerUser $customerUser): Comparison
    {
        $comparison = $this->findComparisonOfCustomerUser($customerUser);

        if ($comparison === null) {
            throw new ComparisonNotFoundException('Current customer user has no comparison.');
        }

        return $comparison;
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return \App\Model\Product\Comparison\Comparison|null
     */
    public function findComparisonOfCustomerUser(CustomerUser $customerUser): ?Comparison
    {
        return $this->comparisonRepository->findByCustomerUser($customerUser);
    }

    /**
     * @param string|null $uuid
     * @return \App\Model\Product\Comparison\Comparison
     */
    public function getComparisonByUuid(?string $uuid): Comparison
    {
        $comparison = $this->comparisonRepository->findByUuid($uuid);

        if ($comparison === null) {
            throw new ComparisonNotFoundException(sprintf('Comparison %s not found.', $uuid));
        }

        $comparison->setUpdatedAtToNow();
        $this->em->flush();

        return $comparison;
    }

    /**
     * @param string|null $uuid
     * @return \App\Model\Product\Comparison\Comparison|null
     */
    public function findComparisonByUuid(?string $uuid): ?Comparison
    {
        try {
            return $this->getComparisonByUuid($uuid);
        } catch (ComparisonNotFoundException) {
            return null;
        }
    }

    /**
     * @param string|null $uuid
     * @return \App\Model\Product\Comparison\Comparison
     */
    public function getOrCreateComparisonByUuid(?string $uuid): Comparison
    {
        $comparison = $this->comparisonRepository->findByUuid($uuid);

        if ($comparison === null) {
            $comparison = new Comparison(null);
            $this->em->persist($comparison);
            $this->em->flush();
        }

        return $comparison;
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param \App\Model\Product\Comparison\Comparison $comparison
     * @return \App\Model\Product\Comparison\Comparison
     */
    public function setCustomerUserToComparison(CustomerUser $customerUser, Comparison $comparison): Comparison
    {
        $comparison->setCustomerUser($customerUser);
        $this->em->flush();

        return $comparison;
    }

    /**
     * @param \App\Model\Product\Comparison\Comparison $loggedCustomerComparison
     * @param \App\Model\Product\Comparison\Comparison $comparisonByUuid
     * @return \App\Model\Product\Comparison\Comparison
     */
    public function mergeComparisons(Comparison $loggedCustomerComparison, Comparison $comparisonByUuid): Comparison
    {
        foreach ($comparisonByUuid->getItems() as $comparedItem) {
            $productFromComparisonByUuid = $comparedItem->getProduct();
            if ($loggedCustomerComparison->isProductInComparison($productFromComparisonByUuid)) {
                continue;
            }

            $newComparedItem = new ComparedItem($loggedCustomerComparison, $productFromComparisonByUuid);
            $this->em->persist($newComparedItem);
            $loggedCustomerComparison->addItem($newComparedItem);
            $this->em->flush();
        }

        $this->em->remove($comparisonByUuid);
        $this->em->flush();

        return $loggedCustomerComparison;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @param string|null $comparisonUuid
     * @return \App\Model\Product\Comparison\Comparison|null
     */
    public function removeProductFromComparison(Product $product, ?CustomerUser $customerUser, ?string $comparisonUuid): ?Comparison
    {
        if ($customerUser !== null) {
            $comparison = $this->getComparisonOfCustomerUser($customerUser);
        } else {
            $comparison = $this->getComparisonByUuid($comparisonUuid);
        }

        $comparedItem = $comparison->getComparedItemByProduct($product);

        $comparison->removeItem($comparedItem);
        $this->em->remove($comparedItem);
        $this->em->flush();

        if ($comparison->getItemsCount() === 0) {
            $this->em->remove($comparison);
            $this->em->flush();

            return null;
        }

        return $comparison;
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @param string|null $comparisonUuid
     */
    public function cleanComparison(?CustomerUser $customerUser, ?string $comparisonUuid): void
    {
        if ($customerUser !== null) {
            $comparison = $this->getComparisonOfCustomerUser($customerUser);
        } else {
            $comparison = $this->getComparisonByUuid($comparisonUuid);
        }

        $this->em->remove($comparison);
        $this->em->flush();
    }
}
