<?php

declare(strict_types=1);

namespace App\Model\Order\Transfer\ScontoBridge;

use App\Component\ScontoBridge\Transfer\ScontoBridgeImportTransferDependency;
use App\Model\Customer\Transfer\ScontoBridge\CustomerTransferScontoBridgeExporter;
use App\Model\Customer\Transfer\ScontoBridge\CustomerTransferScontoBridgeMapperException;
use App\Model\Order\Order;
use App\Model\Order\OrderRepository;
use App\Model\Order\OrderScontoBridgeStatusEnum;
use App\Model\Transfer\TransferIdentificationInterface;
use App\Model\Transfer\TransferLoggerInterface;
use Exception;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;

class OrderTransferScontoBridgeFacade implements TransferIdentificationInterface
{
    /**
     * @var ScontoBridgeImportTransferDependency
     */
    private ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency;

    /**
     * @var OrderRepository
     */
    private OrderRepository $orderRepository;

    /**
     * @var \App\Model\Transfer\TransferLoggerInterface
     */
    private TransferLoggerInterface $logger;

    /**
     * @var OrderTransferScontoBridgeExporter
     */
    private OrderTransferScontoBridgeExporter $orderTransferScontoBridgeExporter;

    /**
     * @var CustomerTransferScontoBridgeExporter
     */
    private CustomerTransferScontoBridgeExporter $customerTransferScontoBridgetExporter;

    public function __construct(
        ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency,
        OrderRepository $orderRepository,
        OrderTransferScontoBridgeExporter $orderTransferScontoBridgeExporter,
        CustomerTransferScontoBridgeExporter $customerTransferScontoBridgetExporter
    ) {
        $this->scontoBridgeImportTransferDependency = $scontoBridgeImportTransferDependency;
        $this->orderRepository = $orderRepository;
        $this->logger = $this->scontoBridgeImportTransferDependency->getTransferLoggerFactory()->getTransferLoggerByIdentifier($this);
        $this->orderTransferScontoBridgeExporter = $orderTransferScontoBridgeExporter;
        $this->customerTransferScontoBridgetExporter = $customerTransferScontoBridgetExporter;
    }

    public function runTransfer(): void
    {
        foreach ($this->getData() as $order) {
            $error = true;
            $this->markOrderScontoBridgeStatusProcessing($order);
            try {
                $this->logger->addDebug(sprintf('START export order id \'%d\'', $order->getId()));

                $this->processItem($order);
                $this->markOrderScontoBridgeStatusDone($order);
                $error = false;

                $this->logger->addDebug(sprintf('DONE export order id \'%d\'', $order->getId()));
            } catch (CustomerTransferScontoBridgeMapperException $e) {
                $this->logger->addError('Cannot map sconto customer user data to bridge format', [
                    'exception' => $e->getMessage()
                ]);
            } catch (OrderTransferScontoBridgeMapperException $e) {
                $this->logger->addError('Cannot map sconto order data to bridge format', [
                    'exception' => $e->getMessage()
                ]);
            } catch (OrderTransferScontoBridgeTransferException $e) {
                $this->logger->addError('Order transfer API error occured', [
                    'response' => $e->getResponseContent(),
                    'httpStatus' => $e->getHttpCode(),
                ]);
            } catch (GuzzleException $e) {
                $response = $request = null;
                if ($e instanceof BadResponseException) {
                    $response = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null;
                    $request = (string)$e->getRequest()->getBody();
                }
                $this->logger->addCritical('Error occured during http transfer', [
                    'exception' => $e->getMessage(),
                    'response' => $response,
                    'request' => $request
                ]);
            } catch (Exception $e) {
                $this->logger->addCritical('Fatal error', [
                    'exception' => $e->getMessage()
                ]);
            }

            if ($error === true) {
                $this->markOrderScontoBridgeStatusError($order);
            }
        }
    }

    /**
     * @param Order $order
     */
    protected function processItem(Order $order): void
    {
        $customerUser = $order->getCustomerUser();
        if ($customerUser !== null) {
            $this->logger->addDebug(sprintf('Exporting customer user id \'%d\'', $customerUser->getId()));
            $this->customerTransferScontoBridgetExporter->exportCustomerUser($customerUser);
            $this->logger->addDebug('Customer export done.');
        } else {
            $this->logger->addDebug('Customer export skipped - empty customer.');
        }

        $this->logger->addDebug('Exporting order data');
        $this->orderTransferScontoBridgeExporter->exportOrderToScontoBridge($order);
        $this->logger->addDebug('Export order data done');
    }

    /**
     * @return \Generator
     */
    protected function getData(): \Generator
    {
        foreach ($this->orderRepository->getAllOrdersNotSentToScontoBridge() as $order) {
            yield $order;
        }
    }

    /**
     * @param Order $order
     */
    private function markOrderScontoBridgeStatusProcessing(Order $order): void
    {
        $this->setOrderScontoBridgeStatus(
            $order,
            OrderScontoBridgeStatusEnum::create(OrderScontoBridgeStatusEnum::STATUS_PROCESSING)
        );
    }

    /**
     * @param Order $order
     */
    private function markOrderScontoBridgeStatusDone(Order $order): void
    {
        $this->setOrderScontoBridgeStatus(
            $order,
            OrderScontoBridgeStatusEnum::create(OrderScontoBridgeStatusEnum::STATUS_DONE)
        );
    }

    /**
     * @param Order $order
     */
    private function markOrderScontoBridgeStatusError(Order $order): void
    {
        $this->setOrderScontoBridgeStatus(
            $order,
            OrderScontoBridgeStatusEnum::create(OrderScontoBridgeStatusEnum::STATUS_ERROR)
        );
    }

    /**
     * @param Order $order
     * @param OrderScontoBridgeStatusEnum $status
     */
    private function setOrderScontoBridgeStatus(Order $order, OrderScontoBridgeStatusEnum $status): void
    {
        $order->setScontoBridgeStatus($status);
        $em = $this->scontoBridgeImportTransferDependency->getEm();
        $em->persist($order);
        $em->flush();
    }

    /**
     * @return string
     */
    public function getTransferName(): string
    {
        return t('Export objednávek do můstku');
    }

    /**
     * @return string
     */
    public function getTransferIdentifier(): string
    {
        return 'ProductExportTransfer';
    }

    /**
     * @return string
     */
    public function getServiceIdentifier(): string
    {
        return 'ScontoBridge';
    }
}
