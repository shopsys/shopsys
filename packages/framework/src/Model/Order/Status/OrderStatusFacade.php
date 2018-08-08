<?php

namespace Shopsys\FrameworkBundle\Model\Order\Status;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailService;
use Shopsys\FrameworkBundle\Model\Order\OrderRepository;

class OrderStatusFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository
     */
    protected $orderStatusRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusService
     */
    protected $orderStatusService;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderRepository
     */
    protected $orderRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade
     */
    protected $mailTemplateFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFactoryInterface
     */
    protected $orderStatusFactory;

    public function __construct(
        EntityManagerInterface $em,
        OrderStatusRepository $orderStatusRepository,
        OrderStatusService $orderStatusService,
        OrderRepository $orderRepository,
        MailTemplateFacade $mailTemplateFacade,
        OrderStatusFactoryInterface $orderStatusFactory
    ) {
        $this->em = $em;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->orderStatusService = $orderStatusService;
        $this->orderRepository = $orderRepository;
        $this->mailTemplateFacade = $mailTemplateFacade;
        $this->orderStatusFactory = $orderStatusFactory;
    }

    public function create(OrderStatusData $orderStatusFormData): \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus
    {
        $orderStatus = $this->orderStatusFactory->create($orderStatusFormData, OrderStatus::TYPE_IN_PROGRESS);
        $this->em->persist($orderStatus);
        $this->em->flush();

        $this->mailTemplateFacade->createMailTemplateForAllDomains(
            OrderMailService::MAIL_TEMPLATE_NAME_PREFIX . $orderStatus->getId()
        );

        return $orderStatus;
    }

    /**
     * @param int $orderStatusId
     */
    public function edit($orderStatusId, OrderStatusData $orderStatusData): \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus
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
        $this->orderStatusService->checkForDelete($orderStatus);
        if ($newOrderStatusId !== null) {
            $newOrderStatus = $this->orderStatusRepository->findById($newOrderStatusId);
            $this->orderStatusRepository->replaceOrderStatus($orderStatus, $newOrderStatus);
        }

        $this->em->remove($orderStatus);
        $this->em->flush();
    }

    /**
     * @param int $orderStatusId
     */
    public function getById($orderStatusId): \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus
    {
        return $this->orderStatusRepository->getById($orderStatusId);
    }

    /**
     * @param int $orderStatusId
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus[]
     */
    public function getAllExceptId($orderStatusId): array
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
    public function getAllIndexedById(): array
    {
        return $this->orderStatusRepository->getAllIndexedById();
    }
}
