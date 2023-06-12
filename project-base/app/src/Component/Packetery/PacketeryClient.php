<?php

declare(strict_types=1);

namespace App\Component\Packetery;

use App\Component\Packetery\Packet\PacketAttributes;
use App\Model\Order\Order;
use App\Model\Order\OrderFacade;
use App\Model\Transfer\TransferIdentificationInterface;
use App\Model\Transfer\TransferLoggerFactory;
use App\Model\Transfer\TransferLoggerInterface;
use SimpleXMLElement;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Twig\Error\Error;

class PacketeryClient implements TransferIdentificationInterface
{
    private TransferLoggerInterface $transferLogger;

    /**
     * @param \App\Component\Packetery\PacketeryConfig $packeteryConfig
     * @param \App\Component\Packetery\PacketeryRenderer $packeteryRenderer
     * @param \Symfony\Contracts\HttpClient\HttpClientInterface $client
     * @param \App\Model\Transfer\TransferLoggerFactory $transferLoggerFactory
     * @param \App\Model\Order\OrderFacade $orderFacade
     */
    public function __construct(
        private PacketeryConfig $packeteryConfig,
        private PacketeryRenderer $packeteryRenderer,
        private HttpClientInterface $client,
        private TransferLoggerFactory $transferLoggerFactory,
        private OrderFacade $orderFacade,
    ) {
    }

    private function getTransferLogger()
    {
        if (!isset($this->transferLogger)) {
            $this->transferLogger = $this->transferLoggerFactory->getTransferLoggerByIdentifier($this);
        }

        return $this->transferLogger;
    }

    /**
     * @param string $xml
     * @return \Symfony\Contracts\HttpClient\ResponseInterface
     */
    private function restApiPostRequest(string $xml): ResponseInterface
    {
        return $this->client->request(
            'POST',
            $this->packeteryConfig->getRestApiUrl(),
            ['body' => $xml],
        );
    }

    /**
     * @param \App\Model\Order\Order $order
     * @return string
     */
    public function createPacketXml(Order $order): string
    {
        $packetAttributes = new PacketAttributes($order);
        return $this->packeteryRenderer->getPacketXml($packetAttributes, $this->packeteryConfig);
    }

    /**
     * @param \App\Model\Order\Order[] $orders
     */
    public function sendPackets(array $orders): void
    {
        $logger = $this->getTransferLogger();
        if (count($orders) === 0) {
            $logger->info('No orders to send to Packetery.');
            $logger->persistAllLoggedTransferIssues();
            return;
        }
        if (!$this->packeteryConfig->isApiAllowed()) {
            $logger->error('Packetery API is not enabled or not set credentials.');
            $logger->persistAllLoggedTransferIssues();
            return;
        }

        foreach ($orders as $order) {
            try {
                $xml = $this->createPacketXml($order);
                $responseXml = $this->restApiPostRequest($xml);
                $this->saveTrackingNumberFromResponse($responseXml, $order);
            } catch (TransportExceptionInterface $transportException) {
                $logger->error(
                    'Transport error - packetery API.',
                    [
                        'msg' => $transportException->getMessage(),
                    ],
                );
            } catch (Error $twigError) {
                $logger->error(
                    'Render error - packetery xml: ',
                    [
                        'msg' => $twigError->getMessage(),
                    ],
                );
            } catch (HttpExceptionInterface $httpException) {
                $logger->error(
                    'Packetery http error: ',
                    [
                        'msg' => $httpException->getMessage(),
                    ],
                );
            }
        }
        $logger->persistAllLoggedTransferIssues();
    }

    /**
     * @param \Symfony\Contracts\HttpClient\ResponseInterface $responseXml
     * @param \App\Model\Order\Order $order
     */
    private function saveTrackingNumberFromResponse(ResponseInterface $responseXml, Order $order)
    {
        $logger = $this->getTransferLogger();
        if ($responseXml->getStatusCode() !== 200 || $responseXml->getContent(false) === '') {
            $logger->error(
                'Bad response from http client.',
                [
                    'statusCode' => $responseXml->getStatusCode(),
                    'content' => $responseXml->getContent(false),
                ],
            );
            return;
        }
        $parsedResponse = new SimpleXMLElement($responseXml->getContent(false));
        if ((string)$parsedResponse->status === 'fault') {
            $logger->error(
                'Response from Packetery fault.',
                [
                    'fault' => (string)$parsedResponse->fault,
                    'statusString' => (string)$parsedResponse->string,
                    'detail' => $parsedResponse->detail->asXML(),
                ],
            );
            return;
        }
        $barcode = (string)$parsedResponse->result->barcode;
        $this->orderFacade->updateTrackingNumber($order, $barcode);
        $logger->info(
            'Send packet data to packetery.',
            [
                'orderNumber' => $order->getNumber(),
                'barcode' => $barcode,
            ],
        );
    }

    /**
     * @return string
     */
    public function getTransferName(): string
    {
        return 'Send packet data to packetery';
    }

    /**
     * @return string
     */
    public function getTransferIdentifier(): string
    {
        return 'packetsExport';
    }

    /**
     * @return string
     */
    public function getServiceIdentifier(): string
    {
        return 'Packetery';
    }
}
