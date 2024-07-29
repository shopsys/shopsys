<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\DataFixture\Exception\UnknownNameTranslationForOrderStatusReferenceNameException;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;

class OrderStatusDataFixture extends AbstractReferenceFixture
{
    public const string ORDER_STATUS_NEW = 'order_status_new';
    public const string ORDER_STATUS_IN_PROGRESS = 'order_status_in_progress';
    public const string ORDER_STATUS_DONE = 'order_status_done';
    public const string ORDER_STATUS_CANCELED = 'order_status_canceled';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade $orderStatusFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusDataFactory $orderStatusDataFactory
     */
    public function __construct(
        private readonly OrderStatusFacade $orderStatusFacade,
        private readonly OrderStatusDataFactoryInterface $orderStatusDataFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->createOrderStatusReference(1, self::ORDER_STATUS_NEW);
        $this->createOrderStatusReference(2, self::ORDER_STATUS_IN_PROGRESS);
        $this->createOrderStatusReference(3, self::ORDER_STATUS_DONE);
        $this->createOrderStatusReference(4, self::ORDER_STATUS_CANCELED);
    }

    /**
     * Order statuses are created (with specific ids) in database migration.
     *
     * @param int $orderStatusId
     * @param string $referenceName
     * @see \Shopsys\FrameworkBundle\Migrations\Version20180603135341
     */
    private function createOrderStatusReference(
        int $orderStatusId,
        string $referenceName,
    ): void {
        $orderStatus = $this->orderStatusFacade->getById($orderStatusId);
        $orderStatusData = $this->orderStatusDataFactory->createFromOrderStatus($orderStatus);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $orderStatusData->name[$locale] = match ($referenceName) {
                self::ORDER_STATUS_NEW => t('New [adjective]', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ORDER_STATUS_IN_PROGRESS => t('In Progress', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ORDER_STATUS_DONE => t('Done', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ORDER_STATUS_CANCELED => t('Canceled', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                default => throw new UnknownNameTranslationForOrderStatusReferenceNameException($referenceName),
            };
        }
        $this->orderStatusFacade->edit($orderStatusId, $orderStatusData);
        $this->addReference($referenceName, $orderStatus);
    }
}
