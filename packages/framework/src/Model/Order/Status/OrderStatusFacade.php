<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMail;
use Shopsys\FrameworkBundle\Model\Order\OrderRepository;

class OrderStatusFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository $orderStatusRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderRepository $orderRepository
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade $mailTemplateFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFactory $orderStatusFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly OrderStatusRepository $orderStatusRepository,
        protected readonly OrderRepository $orderRepository,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly OrderStatusFactory $orderStatusFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData $orderStatusFormData
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus
     */
    public function create(OrderStatusData $orderStatusFormData)
    {
        $orderStatus = $this->orderStatusFactory->create($orderStatusFormData, OrderStatusTypeEnum::TYPE_IN_PROGRESS);
        $this->em->persist($orderStatus);
        $this->em->flush();

        $this->mailTemplateFacade->createMailTemplateForAllDomains(
            OrderMail::getMailTemplateNameByStatus($orderStatus),
        );

        return $orderStatus;
    }

    /**
     * @param int $orderStatusId
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData $orderStatusData
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus
     */
    public function edit($orderStatusId, OrderStatusData $orderStatusData)
    {
        $orderStatus = $this->orderStatusRepository->getById($orderStatusId);
        $orderStatus->edit($orderStatusData);
        $this->em->flush();

        return $orderStatus;
    }

    /**
     * @param int $orderStatusId
     * @param int|null $newOrderStatusId
     */
    public function deleteById($orderStatusId, $newOrderStatusId = null)
    {
        $orderStatus = $this->orderStatusRepository->getById($orderStatusId);
        $orderStatus->checkForDelete();

        if ($newOrderStatusId !== null) {
            $newOrderStatus = $this->orderStatusRepository->findById($newOrderStatusId);
            $this->orderStatusRepository->replaceOrderStatus($orderStatus, $newOrderStatus);
        }

        $this->em->remove($orderStatus);
        $this->em->flush();
    }

    /**
     * @param int $orderStatusId
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus
     */
    public function getById($orderStatusId)
    {
        return $this->orderStatusRepository->getById($orderStatusId);
    }

    /**
     * @param int $orderStatusId
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus[]
     */
    public function getAllExceptId($orderStatusId)
    {
        return $this->orderStatusRepository->getAllExceptId($orderStatusId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus $orderStatus
     * @return bool
     */
    public function isOrderStatusUsed(OrderStatus $orderStatus)
    {
        return $this->orderRepository->isOrderStatusUsed($orderStatus);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus[]
     */
    public function getAll()
    {
        return $this->orderStatusRepository->getAll();
    }

    /**
     * @param string $statusType
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus[]
     */
    public function getAllByType(string $statusType): array
    {
        return $this->orderStatusRepository->getAllByType($statusType);
    }
}
