<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\GiftPlan;

use App\DataFixtures\Demo\ProductDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanRepository;
use Tests\App\Test\TransactionFunctionalTestCase;

class GiftPlanRepositoryTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private GiftPlanRepository $giftPlanRepository;

    public function testFindActiveGiftPlansByMainProduct(): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Product|null $mainProduct */
        $mainProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 14);

        $result = $this->giftPlanRepository->findActiveGiftPlansByMainProductAndDomainId($mainProduct, Domain::FIRST_DOMAIN_ID);

        $this->assertCount(1, $result);
    }
}
