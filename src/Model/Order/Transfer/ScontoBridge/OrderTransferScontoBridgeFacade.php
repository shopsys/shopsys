<?php

declare(strict_types=1);

namespace App\Model\Order\Transfer\ScontoBridge;

use App\Component\ScontoBridge\ScontoBridgeClient;
use App\Component\ScontoBridge\Transfer\ScontoBridgeImportTransferDependency;
use App\Model\Customer\Transfer\ScontoBridge\CustomerTransferScontoBridgeMapper;
use App\Model\Customer\Transfer\ScontoBridge\CustomerTransferScontoBridgeMapperException;
use App\Model\Order\Order;
use App\Model\Order\OrderRepository;
use App\Model\Order\OrderScontoBridgeStatusEnum;
use App\Model\Transfer\TransferIdentificationInterface;
use App\Model\Transfer\TransferLoggerInterface;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class OrderTransferScontoBridgeFacade implements TransferIdentificationInterface
{
    private const URI_ERP_ORDER = '/api/services/app/ErpOrder/SaveErpOrder';
    private const URI_ERP_CUSTOMER = '/api/services/app/ErpUser/SaveErpUser';

    /**
     * @var ScontoBridgeImportTransferDependency
     */
    private ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency;

    /**
     * @var ScontoBridgeClient
     */
    private ScontoBridgeClient $scontoBridgeClient;

    /**
     * @var OrderRepository
     */
    private OrderRepository $orderRepository;

    /**
     * @var CustomerTransferScontoBridgeMapper
     */
    private CustomerTransferScontoBridgeMapper $customerTransferScontoBridgeMapper;

    /**
     * @var OrderTransferScontoBridgeMapper
     */
    private OrderTransferScontoBridgeMapper $orderTransferScontoBridgeMapper;

    /**
     * @var \App\Model\Transfer\TransferLoggerInterface
     */
    private TransferLoggerInterface $logger;

    public function __construct(
        ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency,
        ScontoBridgeClient $scontoBridgeClient,
        OrderRepository $orderRepository,
        CustomerTransferScontoBridgeMapper $customerTransferScontoBridgeMapper,
        OrderTransferScontoBridgeMapper $orderTransferScontoBridgeMapper
    ) {
        $this->scontoBridgeImportTransferDependency = $scontoBridgeImportTransferDependency;
        $this->scontoBridgeClient = $scontoBridgeClient;
        $this->orderRepository = $orderRepository;
        $this->customerTransferScontoBridgeMapper = $customerTransferScontoBridgeMapper;
        $this->orderTransferScontoBridgeMapper = $orderTransferScontoBridgeMapper;
        $this->logger = $this->scontoBridgeImportTransferDependency->getTransferLoggerFactory()->getTransferLoggerByIdentifier($this);
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
                $this->logger->addCritical('Error occured during http transfer', [
                    'exception' => $e->getMessage()
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
     *
     * @throws CustomerTransferScontoBridgeMapperException
     * @throws OrderTransferScontoBridgeMapperException
     * @throws OrderTransferScontoBridgeTransferException
     */
    protected function processItem(Order $order): void
    {
        $customerUser = $order->getCustomerUser();
        if ($customerUser !== null) {
            $this->logger->addDebug(sprintf('Exporting customer user id \'%d\'', $customerUser->getId()));

            $user = $this->customerTransferScontoBridgeMapper->mapCustomerUserToScontoBridgeCustomerData($customerUser);

            $uri = self::URI_ERP_CUSTOMER;
            $response = $this->scontoBridgeClient->post($uri, $user);
            if ($this->transferFailed($response)) {
                throw $this->createTransferException($response);
            }
            $this->logger->addDebug('Customer export done.');
        } else {
            $this->logger->addDebug('Customer export skipped - empty customer.');
        }

        $this->logger->addDebug('Exporting order data');
        $erpOrder = $this->orderTransferScontoBridgeMapper->mapOrderToScontoBridgeOrderData($order);
        $uri = self::URI_ERP_ORDER;
        $response = $this->scontoBridgeClient->post($uri, $erpOrder);
        if ($this->transferFailed($response)) {
            throw $this->createTransferException($response);
        }
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
     * @param $order
     */
    private function markOrderScontoBridgeStatusError($order)
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

    /**
     * @param ResponseInterface $response
     * @return OrderTransferScontoBridgeTransferException
     */
    private function createTransferException(ResponseInterface $response): OrderTransferScontoBridgeTransferException
    {
        return new OrderTransferScontoBridgeTransferException(
            $response->getStatusCode(),
            $response->getBody()->getContents()
        );
    }

    /**
     * @param ResponseInterface $response
     * @return bool
     */
    private function transferFailed(ResponseInterface $response): bool
    {
        $responseContent = $response->getBody()->getContents();
        $decodedContent = json_decode($responseContent, true);

        return $response->getStatusCode() > Response::HTTP_OK
            || $decodedContent === null
            || ($decodedContent['success'] ?? false) === false;
    }
}
