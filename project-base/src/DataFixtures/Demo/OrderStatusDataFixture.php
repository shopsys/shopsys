<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;

class OrderStatusDataFixture extends AbstractReferenceFixture
{
    public const ORDER_STATUS_NEW = 'order_status_new';
    public const ORDER_STATUS_IN_PROGRESS = 'order_status_in_progress';
    public const ORDER_STATUS_DONE = 'order_status_done';
    public const ORDER_STATUS_CANCELED = 'order_status_canceled';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade
     */
    protected $orderStatusFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusDataFactoryInterface
     */
    protected $orderStatusDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade $orderStatusFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusDataFactoryInterface $orderStatusDataFactory
     */
    public function __construct(
        OrderStatusFacade $orderStatusFacade,
        Domain $domain,
        OrderStatusDataFactoryInterface $orderStatusDataFactory
    ) {
        $this->orderStatusFacade = $orderStatusFacade;
        $this->domain = $domain;
        $this->orderStatusDataFactory = $orderStatusDataFactory;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
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
    protected function createOrderStatusReference(
        $orderStatusId,
        $referenceName
    ) {
        $orderStatus = $this->orderStatusFacade->getById($orderStatusId);
        $orderStatusData = $this->orderStatusDataFactory->createFromOrderStatus($orderStatus);
        foreach ($this->domain->getAllLocales() as $locale) {
            switch ($referenceName) {
                case self::ORDER_STATUS_NEW:
                    $orderStatusData->name[$locale] = t('New [adjective]', [], 'dataFixtures', $locale);
                    break;
                case self::ORDER_STATUS_IN_PROGRESS:
                    $orderStatusData->name[$locale] = t('In Progress', [], 'dataFixtures', $locale);
                    break;
                case self::ORDER_STATUS_DONE:
                    $orderStatusData->name[$locale] = t('Done', [], 'dataFixtures', $locale);
                    break;
                case self::ORDER_STATUS_CANCELED:
                    $orderStatusData->name[$locale] = t('Canceled', [], 'dataFixtures', $locale);
                    break;
                default:
                    throw new \Shopsys\FrameworkBundle\Component\DataFixture\Exception\UnknownNameTranslationForOrderStatusReferenceNameException($referenceName);
            }
        }
        $this->orderStatusFacade->edit($orderStatusId, $orderStatusData);
        $this->addReference($referenceName, $orderStatus);
    }
}
