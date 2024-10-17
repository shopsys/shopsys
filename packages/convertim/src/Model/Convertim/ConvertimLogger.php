<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Convertim;

use Psr\Log\LoggerInterface;
use Shopsys\ConvertimBundle\Model\Convertim\Exception\ConvertimException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class ConvertimLogger
{
    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(protected readonly LoggerInterface $logger)
    {
    }

    /**
     * @param \Shopsys\ConvertimBundle\Model\Convertim\Exception\ConvertimException $exception
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function logConvertimException(ConvertimException $exception): JsonResponse
    {
        $this->logger->warning('Convertim exception', [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'context' => $exception->getContext(),
        ]);

        return new JsonResponse(['error' => $exception->getMessage()], $exception->getCode());
    }

    /**
     * @param \Throwable $exception
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function logGenericException(Throwable $exception): JsonResponse
    {
        $this->logger->error('Convertim internal server error', [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'trace' => $exception->getTraceAsString(),
        ]);

        return new JsonResponse([
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'trace' => $exception->getTraceAsString(),
        ], 500);
    }
}
