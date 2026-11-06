<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\GiftPlan;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlan;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanRepository;
use Tests\App\Test\TransactionFunctionalTestCase;

class GiftPlanRepositoryTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private GiftPlanRepository $giftPlanRepository;

    public function testFindActiveGiftProductIdsByMainProductIds(): void
    {
        $mainProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 14, Product::class);
        $giftProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 72, Product::class);

        $result = $this->giftPlanRepository->findActiveGiftProductIdsByMainProductIds([$mainProduct->getId()], Domain::FIRST_DOMAIN_ID);

        $this->assertSame([$mainProduct->getId() => [$giftProduct->getId()]], $result);
    }

    public function testActiveGiftProductIdsOfAllDomainsAreReturnedAtOnce(): void
    {
        $mainProductWithSaleExcludedGiftId = $this->getProductId(10);
        $mainProductWithValidGiftId = $this->getProductId(14);
        $mainProductWithExpiredGiftId = $this->getProductId(77);
        $expectedGiftProductIdsIndexedByMainProductId = [
            $mainProductWithSaleExcludedGiftId => [$this->getProductId(71)],
            $mainProductWithValidGiftId => [$this->getProductId(72)],
        ];
        $expectedGiftProductIdsIndexedByDomainIdAndMainProductId = array_fill_keys(
            $this->domainsForDataFixtureProvider->getAllowedDemoDataDomainIds(),
            $expectedGiftProductIdsIndexedByMainProductId,
        );

        $giftProductIdsIndexedByDomainIdAndMainProductId = $this->giftPlanRepository->getActiveGiftProductIdsIndexedByDomainIdAndMainProductId([
            $mainProductWithExpiredGiftId,
            $mainProductWithValidGiftId,
            $mainProductWithSaleExcludedGiftId,
        ]);

        ksort($giftProductIdsIndexedByDomainIdAndMainProductId);
        $this->assertSame($expectedGiftProductIdsIndexedByDomainIdAndMainProductId, $giftProductIdsIndexedByDomainIdAndMainProductId);
    }

    public function testGetMainProductIdsByGiftProductIds(): void
    {
        $mainProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 14, Product::class);
        $giftProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 72, Product::class);

        $result = $this->giftPlanRepository->getMainProductIdsByGiftProductIds([$giftProduct->getId()]);

        $this->assertSame([$mainProduct->getId()], $result);
    }

    public function testGetAllMainProductIds(): void
    {
        $giftPlans = $this->em->getRepository(GiftPlan::class)->findAll();
        $expectedMainProductIds = [];

        foreach ($giftPlans as $giftPlan) {
            $mainProducts = $giftPlan->getMainProducts();

            foreach ($mainProducts as $mainProduct) {
                $expectedMainProductIds[] = $mainProduct->getId();
            }
        }

        $expectedMainProductIds = array_values(array_unique($expectedMainProductIds));
        sort($expectedMainProductIds);
        $mainProductIds = $this->giftPlanRepository->getAllMainProductIds();
        sort($mainProductIds);

        $this->assertSame($expectedMainProductIds, $mainProductIds);
    }

    private function getProductId(int $productNumber): int
    {
        return $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productNumber, Product::class)->getId();
    }
}
