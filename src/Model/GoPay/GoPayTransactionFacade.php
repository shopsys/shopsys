<?php

declare(strict_types=1);

namespace App\Model\GoPay;

use App\Model\Order\Order;
use Doctrine\ORM\EntityManagerInterface;

class GoPayTransactionFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \App\Model\GoPay\GoPayTransactionRepository
     */
    private $goPayTransactionRepository;

    /**
     * @var \App\Model\GoPay\GoPayOnCurrentDomainFacade
     */
    private $goPayFacadeOnCurrentDomain;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\GoPay\GoPayTransactionRepository $goPayTransactionRepository
     * @param \App\Model\GoPay\GoPayOnCurrentDomainFacade $goPayFacadeOnCurrentDomain
     */
    public function __construct(
        EntityManagerInterface $em,
        GoPayTransactionRepository $goPayTransactionRepository,
        GoPayOnCurrentDomainFacade $goPayFacadeOnCurrentDomain
    ) {
        $this->em = $em;
        $this->goPayTransactionRepository = $goPayTransactionRepository;
        $this->goPayFacadeOnCurrentDomain = $goPayFacadeOnCurrentDomain;
    }

    /**
     * @param \App\Model\GoPay\GoPayTransactionData $goPayTransactionData
     * @return \App\Model\GoPay\GoPayTransaction
     */
    public function create(GoPayTransactionData $goPayTransactionData): GoPayTransaction
    {
        $goPayTransaction = new GoPayTransaction($goPayTransactionData);

        $this->em->persist($goPayTransaction);
        $this->em->flush();

        return $goPayTransaction;
    }

    /**
     * @param \App\Model\Order\Order $order
     * @param  string $goPayId
     * @return \App\Model\GoPay\GoPayTransaction
     */
    public function createNewTransactionByOrder(Order $order, string $goPayId): GoPayTransaction
    {
        $goPayTransactionData = new GoPayTransactionData($goPayId, $order);

        return $this->create($goPayTransactionData);
    }

    /**
     * @param \App\Model\Order\Order $order
     */
    public function updateOrderTransactions(Order $order): void
    {
        if ($order->isGoPayPaid()) {
            return;
        }

        $goPayTransactions = $this->goPayTransactionRepository->findAllByOrder($order);
        $goPayResponsesData = $this->goPayFacadeOnCurrentDomain->getPaymentStatusesResponseDataByGoPayTransactionAndDomainId(
            $goPayTransactions,
            $order->getDomainId()
        );
        $toFlush = false;

        foreach ($goPayResponsesData as $goPayStatusResponseData) {
            $goPayStatusResponse = $goPayStatusResponseData->response;
            if (array_key_exists('state', (array) $goPayStatusResponse->json)) {
                $goPayTransaction = $goPayStatusResponseData->goPayTransaction;
                $goPayTransaction->setGoPayStatus($goPayStatusResponse->json['state']);
                $toFlush = true;
            }
        }

        if ($toFlush) {
            $this->em->flush();
        }
    }

    /**
     * @param \App\Model\Order\Order $order
     * @return bool
     */
    public function isOrderPaid(Order $order): bool
    {
        return $this->goPayTransactionRepository->isOrderPaid($order);
    }
}
