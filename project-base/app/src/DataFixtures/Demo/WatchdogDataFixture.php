<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Watchdog\WatchdogDataFactory;
use Shopsys\FrameworkBundle\Model\Watchdog\WatchdogFacade;
use Symfony\Component\Clock\DatePoint;

class WatchdogDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    private const string ATTRIBUTE_EMAIL_KEY = 'email';
    private const string ATTRIBUTE_PRODUCT_KEY = 'productId';

    public function __construct(
        protected readonly WatchdogFacade $watchdogFacade,
        protected readonly WatchdogDataFactory $watchdogDataFactory,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        $this->createWatchdogs();
    }

    /**
     * @return array[]
     */
    private function getDataForWatchdogs(): array
    {
        return [
            [
                self::ATTRIBUTE_EMAIL_KEY => 'yolande.liliane@example.com',
                self::ATTRIBUTE_PRODUCT_KEY => '1',
            ],
            [
                self::ATTRIBUTE_EMAIL_KEY => 'emma.smith@example.com',
                self::ATTRIBUTE_PRODUCT_KEY => '1',
            ],
            [
                self::ATTRIBUTE_EMAIL_KEY => 'john.doe@example.com',
                self::ATTRIBUTE_PRODUCT_KEY => '3',
            ],
            [
                self::ATTRIBUTE_EMAIL_KEY => 'jane.doe@example.com',
                self::ATTRIBUTE_PRODUCT_KEY => '4',
            ],
            [
                self::ATTRIBUTE_EMAIL_KEY => 'alice.jones@example.com',
                self::ATTRIBUTE_PRODUCT_KEY => '5',
            ],
            [
                self::ATTRIBUTE_EMAIL_KEY => 'robert.brown@example.com',
                self::ATTRIBUTE_PRODUCT_KEY => '6',
            ],
            [
                self::ATTRIBUTE_EMAIL_KEY => 'charlie.white@example.com',
                self::ATTRIBUTE_PRODUCT_KEY => '7',
            ],
            [
                self::ATTRIBUTE_EMAIL_KEY => 'lucy.adams@example.com',
                self::ATTRIBUTE_PRODUCT_KEY => '8',
            ],
            [
                self::ATTRIBUTE_EMAIL_KEY => 'george.evans@example.com',
                self::ATTRIBUTE_PRODUCT_KEY => '1',
            ],
            [
                self::ATTRIBUTE_EMAIL_KEY => 'olivia.martin@example.com',
                self::ATTRIBUTE_PRODUCT_KEY => '10',
            ],
            [
                self::ATTRIBUTE_EMAIL_KEY => 'james.wilson@example.com',
                self::ATTRIBUTE_PRODUCT_KEY => '11',
            ],
            [
                self::ATTRIBUTE_EMAIL_KEY => 'sophia.moore@example.com',
                self::ATTRIBUTE_PRODUCT_KEY => '12',
            ],
            [
                self::ATTRIBUTE_EMAIL_KEY => 'henry.jackson@example.com',
                self::ATTRIBUTE_PRODUCT_KEY => '13',
            ],
            [
                self::ATTRIBUTE_EMAIL_KEY => 'mia.thomas@example.com',
                self::ATTRIBUTE_PRODUCT_KEY => '14',
            ],
            [
                self::ATTRIBUTE_EMAIL_KEY => 'liam.harris@example.com',
                self::ATTRIBUTE_PRODUCT_KEY => '15',
            ],
            [
                self::ATTRIBUTE_EMAIL_KEY => 'amelia.clark@example.com',
                self::ATTRIBUTE_PRODUCT_KEY => '16',
            ],
        ];
    }

    private function createWatchdogFromArray(array $data, int $domainId): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $data[self::ATTRIBUTE_PRODUCT_KEY]);

        $watchdogData = $this->watchdogDataFactory->createByDomainId($domainId);
        $watchdogData->email = $data[self::ATTRIBUTE_EMAIL_KEY];
        $watchdogData->product = $product;

        $createdAt = (new DatePoint())->modify('-' . random_int(1, 365) . ' days');

        $watchdogData->createdAt = $createdAt;
        $watchdogData->validUntil = $createdAt->modify('+ 2 years');

        $this->watchdogFacade->create($watchdogData);
    }

    private function createWatchdogs(): void
    {
        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomainIds() as $domainId) {
            foreach ($this->getDataForWatchdogs() as $data) {
                $this->createWatchdogFromArray($data, $domainId);
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
            ProductDataFixture::class,
        ];
    }
}
