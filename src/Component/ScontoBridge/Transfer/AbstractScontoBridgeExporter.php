<?php
declare(strict_types=1);

namespace App\Component\ScontoBridge\Transfer;

use App\Model\Order\Transfer\ScontoBridge\OrderTransferScontoBridgeTransferException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class AbstractScontoBridgeExporter
{
    /**
     * @param ResponseInterface $response
     * @return bool
     */
    protected function transferFailed(ResponseInterface $response): bool
    {
        $responseContent = $response->getBody()->getContents();
        $decodedContent = json_decode($responseContent, true);

        return $response->getStatusCode() > Response::HTTP_OK
            || $decodedContent === null
            || ($decodedContent['success'] ?? false) === false;
    }

    /**
     * @param ResponseInterface $response
     * @return OrderTransferScontoBridgeTransferException
     */
    protected function createTransferException(ResponseInterface $response): OrderTransferScontoBridgeTransferException
    {
        return new OrderTransferScontoBridgeTransferException(
            $response->getStatusCode(),
            $response->getBody()->getContents()
        );
    }
}
