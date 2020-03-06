<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Pricing\Group;

use App\DataFixtures\Demo\PricingGroupDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductCalculatedPrice;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class PricingGroupFacadeTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade
     * @inject
     */
    private $pricingGroupFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculator
     * @inject
     */
    private $productPriceRecalculator;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade
     * @inject
     */
    private $customerUserFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface
     * @inject
     */
    private $customerUserDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface
     * @inject
     */
    private $customerUserUpdateDataFactory;

    public function testCreate()
    {
        $em = $this->getEntityManager();
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        $pricingGroupData = new PricingGroupData();
        $pricingGroupData->name = 'pricing_group_name';
        $domainId = Domain::FIRST_DOMAIN_ID;
        $pricingGroup = $this->pricingGroupFacade->create($pricingGroupData, $domainId);
        $this->productPriceRecalculator->runAllScheduledRecalculations();
        $productCalculatedPrice = $em->getRepository(ProductCalculatedPrice::class)->findOneBy([
            'product' => $product,
            'pricingGroup' => $pricingGroup,
        ]);

        $this->assertNotNull($productCalculatedPrice);
    }

    public function testDeleteAndReplace()
    {
        $em = $this->getEntityManager();

        $pricingGroupData = new PricingGroupData();
        $pricingGroupData->name = 'name';
        $pricingGroupToDelete = $this->pricingGroupFacade->create($pricingGroupData, Domain::FIRST_DOMAIN_ID);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroupToReplaceWith */
        $pricingGroupToReplaceWith = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->customerUserFacade->getCustomerUserById(1);

        $customerUserData = $this->customerUserDataFactory->createFromCustomerUser($customerUser);
        $customerUserData->pricingGroup = $pricingGroupToDelete;
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->create();
        $customerUserUpdateData->customerUserData = $customerUserData;
        $this->customerUserFacade->editByAdmin($customerUser->getId(), $customerUserUpdateData);

        $this->pricingGroupFacade->delete($pricingGroupToDelete->getId(), $pricingGroupToReplaceWith->getId());

        $em->refresh($customerUser);

        $this->assertEquals($pricingGroupToReplaceWith, $customerUser->getPricingGroup());
    }
}
