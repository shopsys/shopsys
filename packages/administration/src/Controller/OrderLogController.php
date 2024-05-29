<?php

declare(strict_types=1);

namespace Shopsys\Administration\Controller;

use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogFacade;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\Grid\EntityLogGridFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderLogController extends AbstractController
{
    /**
     */
    public function __construct(
        protected readonly EntityLogGridFactory $entityLogGridFactory,
        protected readonly OrderFacade $orderFacade,
    ) {
    }

    #[Route('/%admin_url%-new/administrator-log-table')]
    public function tableAction(?int $orderId): Response
    {
        $order = $this->orderFacade->getById($orderId);
        $entityLogGrid = $this->entityLogGridFactory->createByEntityNameAndEntityId(
            EntityLogFacade::getEntityNameByEntity($order),
            $order->getId(),
        );

        return $this->render('@ShopsysAdministration/Order/orderLog.html.twig', [
            'entityLogGridView' => $entityLogGrid->createView(),
        ]);
    }
}
