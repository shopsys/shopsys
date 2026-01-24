<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMail;
use Shopsys\FrameworkBundle\Model\Order\OrderRepository;

class OrderStatusFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly OrderStatusRepository $orderStatusRepository,
        protected readonly OrderRepository $orderRepository,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly OrderStatusFactory $orderStatusFactory,
    ) {
    }

    public function create(OrderStatusData $orderStatusFormData): OrderStatus
    {
        $orderStatus = $this->orderStatusFactory->create($orderStatusFormData, OrderStatusTypeEnum::TYPE_IN_PROGRESS);
        $this->em->persist($orderStatus);
        $this->em->flush();

        $this->mailTemplateFacade->createMailTemplateForAllDomains(
            OrderMail::getMailTemplateNameByStatus($orderStatus),
            $orderStatus,
        );

        return $orderStatus;
    }

    public function edit(int $orderStatusId, OrderStatusData $orderStatusData): OrderStatus
    {
        $orderStatus = $this->orderStatusRepository->getById($orderStatusId);
        $orderStatus->edit($orderStatusData);
        $this->em->flush();

        return $orderStatus;
    }

    public function deleteById(int $orderStatusId, ?int $newOrderStatusId = null): void
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

    public function getById(int $orderStatusId): OrderStatus
    {
        return $this->orderStatusRepository->getById($orderStatusId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus[]
     */
    public function getAllExceptId(int $orderStatusId): array
    {
        return $this->orderStatusRepository->getAllExceptId($orderStatusId);
    }

    public function isOrderStatusUsed(OrderStatus $orderStatus): bool
    {
        return $this->orderRepository->isOrderStatusUsed($orderStatus);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus[]
     */
    public function getAll(): array
    {
        return $this->orderStatusRepository->getAll();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus[]
     */
    public function getAllByType(string $statusType): array
    {
        return $this->orderStatusRepository->getAllByType($statusType);
    }

    public function getByType(string $statusType): OrderStatus
    {
        return $this->orderStatusRepository->getByType($statusType);
    }
}
