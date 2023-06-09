<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Pricing\Group;

use App\DataFixtures\Demo\PricingGroupDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductCalculatedPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculator;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class PricingGroupFacadeTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @inject
     */
    private PricingGroupFacade $pricingGroupFacade;

    /**
     * @inject
     */
    private ProductPriceRecalculator $productPriceRecalculator;

    /**
     * @inject
     */
    private CustomerUserFacade $customerUserFacade;

    /**
     * @inject
     */
    private CustomerUserDataFactoryInterface $customerUserDataFactory;

    /**
     * @inject
     */
    private CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory;

    public function testCreate()
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        $pricingGroupData = new PricingGroupData();
        $pricingGroupData->name = 'pricing_group_name';
        $domainId = Domain::FIRST_DOMAIN_ID;
        $pricingGroup = $this->pricingGroupFacade->create($pricingGroupData, $domainId);
        $this->productPriceRecalculator->runAllScheduledRecalculations();
        $productCalculatedPrice = $this->em->getRepository(ProductCalculatedPrice::class)->findOneBy([
            'product' => $product,
            'pricingGroup' => $pricingGroup,
        ]);

        $this->assertNotNull($productCalculatedPrice);
    }

    public function testDeleteAndReplace()
    {
        $pricingGroupData = new PricingGroupData();
        $pricingGroupData->name = 'name';
        $pricingGroupToDelete = $this->pricingGroupFacade->create($pricingGroupData, Domain::FIRST_DOMAIN_ID);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroupToReplaceWith */
        $pricingGroupToReplaceWith = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID
        );
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->customerUserFacade->getCustomerUserById(1);

        $customerUserData = $this->customerUserDataFactory->createFromCustomerUser($customerUser);
        $customerUserData->pricingGroup = $pricingGroupToDelete;
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->create();
        $customerUserUpdateData->customerUserData = $customerUserData;
        $this->customerUserFacade->editByAdmin($customerUser->getId(), $customerUserUpdateData);

        $this->pricingGroupFacade->delete($pricingGroupToDelete->getId(), $pricingGroupToReplaceWith->getId());

        $this->em->refresh($customerUser);

        $this->assertEquals($pricingGroupToReplaceWith, $customerUser->getPricingGroup());
    }
}
