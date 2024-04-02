<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Packetery;

use Shopsys\FrameworkBundle\Component\Packetery\Packet\PacketAttributes;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Transfer\TransferIdentificationInterface;
use Shopsys\FrameworkBundle\Model\Transfer\TransferLoggerFactory;
use Shopsys\FrameworkBundle\Model\Transfer\TransferLoggerInterface;
use SimpleXMLElement;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Twig\Error\Error;

class PacketeryClient implements TransferIdentificationInterface
{
    protected TransferLoggerInterface $transferLogger;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Packetery\PacketeryConfig $packeteryConfig
     * @param \Shopsys\FrameworkBundle\Component\Packetery\PacketeryRenderer $packeteryRenderer
     * @param \Symfony\Contracts\HttpClient\HttpClientInterface $client
     * @param \Shopsys\FrameworkBundle\Model\Transfer\TransferLoggerFactory $transferLoggerFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     */
    public function __construct(
        protected readonly PacketeryConfig $packeteryConfig,
        protected readonly PacketeryRenderer $packeteryRenderer,
        protected readonly HttpClientInterface $client,
        protected readonly TransferLoggerFactory $transferLoggerFactory,
        protected readonly OrderFacade $orderFacade,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transfer\TransferLoggerInterface
     */
    protected function getTransferLogger(): TransferLoggerInterface
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
    protected function restApiPostRequest(string $xml): ResponseInterface
    {
        return $this->client->request(
            'POST',
            $this->packeteryConfig->getRestApiUrl(),
            ['body' => $xml],
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return string
     */
    public function createPacketXml(Order $order): string
    {
        $packetAttributes = new PacketAttributes($order);

        return $this->packeteryRenderer->getPacketXml($packetAttributes, $this->packeteryConfig);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order[] $orders
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
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    protected function saveTrackingNumberFromResponse(ResponseInterface $responseXml, Order $order): void
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
