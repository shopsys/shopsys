<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Customer\User\CustomerUser;
use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeDataFactory;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeFacade;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\LoginTypeEnum;

class CustomerUserLoginTypeDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeDataFactory $customerUserLoginTypeDataFactory
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeFacade $customerUserLoginTypeFacade
     * @param \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
     */
    public function __construct(
        protected readonly CustomerUserLoginTypeDataFactory $customerUserLoginTypeDataFactory,
        protected readonly CustomerUserLoginTypeFacade $customerUserLoginTypeFacade,
        protected readonly CustomerUserFacade $customerUserFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function load(ObjectManager $manager): void
    {
        foreach ($this->customerUserFacade->getAll() as $customerUser) {
            $this->createWebLoginType($customerUser);

            if ($customerUser->getId() === 1) {
                $this->createFacebookLoginType($customerUser);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDependencies(): array
    {
        return [
            CustomerUserDataFixture::class,
        ];
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     */
    private function createWebLoginType(CustomerUser $customerUser): void
    {
        $customerUserLoginTypeWebData = $this->customerUserLoginTypeDataFactory->create(
            $customerUser,
            LoginTypeEnum::WEB,
        );
        $customerUserLoginTypeWebData->lastLoggedInAt = new DateTime('-1 hour');
        $this->customerUserLoginTypeFacade->updateCustomerUserLoginTypes($customerUserLoginTypeWebData);
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     */
    private function createFacebookLoginType(CustomerUser $customerUser): void
    {
        $customerUserLoginTypeFacebookData = $this->customerUserLoginTypeDataFactory->create(
            $customerUser,
            LoginTypeEnum::FACEBOOK,
            '1234567890',
        );
        $customerUserLoginTypeFacebookData->lastLoggedInAt = new DateTime();
        $this->customerUserLoginTypeFacade->updateCustomerUserLoginTypes($customerUserLoginTypeFacebookData);
    }
}
