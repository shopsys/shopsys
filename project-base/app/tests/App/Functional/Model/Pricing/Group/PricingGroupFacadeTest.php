<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Pricing\Group;

use App\DataFixtures\Demo\PricingGroupDataFixture;
use App\Model\Customer\BillingAddressDataFactory;
use App\Model\Customer\User\CustomerUser;
use App\Model\Customer\User\CustomerUserDataFactory;
use App\Model\Customer\User\CustomerUserFacade;
use App\Model\Customer\User\CustomerUserUpdateDataFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\Exception\InvalidPricingGroupReplacementException;
use Shopsys\FrameworkBundle\Model\Pricing\Group\Exception\PricingGroupIsUsedException;
use Shopsys\FrameworkBundle\Model\Pricing\Group\Exception\PricingGroupNotFoundException;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

class PricingGroupFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private PricingGroupFacade $pricingGroupFacade;

    /**
     * @inject
     */
    private PricingGroupSettingFacade $pricingGroupSettingFacade;

    /**
     * @inject
     */
    private CustomerUserFacade $customerUserFacade;

    /**
     * @inject
     */
    private CustomerUserDataFactory $customerUserDataFactory;

    /**
     * @inject
     */
    private CustomerUserUpdateDataFactory $customerUserUpdateDataFactory;

    /**
     * @inject
     */
    private BillingAddressDataFactory $billingAddressDataFactory;

    public function testDeleteAndReplace(): void
    {
        $domainId = Domain::FIRST_DOMAIN_ID;

        $pricingGroupData = new PricingGroupData();
        $pricingGroupData->name = 'name';
        $pricingGroupToDelete = $this->pricingGroupFacade->create($pricingGroupData, $domainId);
        $pricingGroupToReplaceWith = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            $domainId,
            PricingGroup::class,
        );
        $customerUser = $this->customerUserFacade->getCustomerUserById(1);
        $this->assignPricingGroupToCustomerUser($customerUser, $pricingGroupToDelete);

        $this->pricingGroupFacade->delete($pricingGroupToDelete->getId(), $pricingGroupToReplaceWith->getId());

        $this->em->refresh($customerUser);

        $this->assertEquals($pricingGroupToReplaceWith, $customerUser->getPricingGroup());
    }

    public function testDeleteDefaultPricingGroupSetsReplacementAsDefault(): void
    {
        $domainId = Domain::FIRST_DOMAIN_ID;

        $defaultPricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId);
        $pricingGroupToReplaceWith = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_VIP,
            $domainId,
            PricingGroup::class,
        );

        $this->pricingGroupFacade->delete($defaultPricingGroup->getId(), $pricingGroupToReplaceWith->getId());

        $this->assertSame(
            $pricingGroupToReplaceWith,
            $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId),
        );
    }

    public function testDeleteDefaultPricingGroupWithoutReplacementThrowsException(): void
    {
        $defaultPricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId(Domain::FIRST_DOMAIN_ID);

        $this->expectException(PricingGroupIsUsedException::class);

        $this->pricingGroupFacade->delete($defaultPricingGroup->getId());
    }

    public function testDeletePricingGroupAssignedToCustomerWithoutReplacementThrowsException(): void
    {
        $pricingGroupData = new PricingGroupData();
        $pricingGroupData->name = 'assigned to customer';
        $pricingGroupAssignedToCustomer = $this->pricingGroupFacade->create($pricingGroupData, Domain::FIRST_DOMAIN_ID);
        $customerUser = $this->customerUserFacade->getCustomerUserById(1);
        $this->assignPricingGroupToCustomerUser($customerUser, $pricingGroupAssignedToCustomer);

        $this->expectException(PricingGroupIsUsedException::class);

        $this->pricingGroupFacade->delete($pricingGroupAssignedToCustomer->getId());
    }

    public function testDeleteUnusedPricingGroupWithoutReplacement(): void
    {
        $pricingGroupData = new PricingGroupData();
        $pricingGroupData->name = 'unused';
        $pricingGroup = $this->pricingGroupFacade->create($pricingGroupData, Domain::FIRST_DOMAIN_ID);
        $pricingGroupId = $pricingGroup->getId();

        $this->pricingGroupFacade->delete($pricingGroupId);

        $this->expectException(PricingGroupNotFoundException::class);

        $this->pricingGroupFacade->getById($pricingGroupId);
    }

    public function testDeleteWithReplacementFromAnotherDomainThrowsException(): void
    {
        if (!$this->domain->isMultidomain()) {
            $this->markTestSkipped('Test requires at least two domains.');
        }

        $pricingGroupToDelete = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
            PricingGroup::class,
        );
        $pricingGroupFromAnotherDomain = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::SECOND_DOMAIN_ID,
            PricingGroup::class,
        );

        $this->expectException(InvalidPricingGroupReplacementException::class);

        $this->pricingGroupFacade->delete($pricingGroupToDelete->getId(), $pricingGroupFromAnotherDomain->getId());
    }

    public function testDeleteWithItselfAsReplacementThrowsException(): void
    {
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
            PricingGroup::class,
        );

        $this->expectException(InvalidPricingGroupReplacementException::class);

        $this->pricingGroupFacade->delete($pricingGroup->getId(), $pricingGroup->getId());
    }

    private function assignPricingGroupToCustomerUser(CustomerUser $customerUser, PricingGroup $pricingGroup): void
    {
        $customerUserData = $this->customerUserDataFactory->createFromCustomerUser($customerUser);
        $customerUserData->pricingGroup = $pricingGroup;

        /** @var \App\Model\Customer\BillingAddress $billingAddress */
        $billingAddress = $customerUser->getCustomer()->getBillingAddress();
        $billingAddressData = $this->billingAddressDataFactory->createFromBillingAddress($billingAddress);

        $customerUserUpdateData = $this->customerUserUpdateDataFactory->create();
        $customerUserUpdateData->customerUserData = $customerUserData;
        $customerUserUpdateData->billingAddressData = $billingAddressData;

        $this->customerUserFacade->editByAdmin($customerUser->getId(), $customerUserUpdateData);
    }
}
