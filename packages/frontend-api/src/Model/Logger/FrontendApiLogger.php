<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Logger;

use Overblog\GraphQLBundle\Validator\Exception\ArgumentsValidationException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Validator\ConstraintViolation;

class FrontendApiLogger implements LoggerInterface
{
    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param bool $isValidationLoggedAsError
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly bool $isValidationLoggedAsError,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = []): void
    {
        if (isset($context['exception'])) {
            $throwable = $context['exception'];

            if ($throwable instanceof ArgumentsValidationException) {
                $level = $this->isValidationLoggedAsError ? LogLevel::ERROR : LogLevel::INFO;
                $context['violations'] = [];

                foreach ($throwable->getViolations() as $violation) {
                    if ($violation instanceof ConstraintViolation) {
                        $context['violations'][] = $violation->getPropertyPath() . ': ' . $violation->getMessage();

                        continue;
                    }

                    $context['violations'][] = $violation;
                }
            }
        }

        $this->logger->log($level, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function emergency($message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function alert($message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function critical($message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function notice($message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function info($message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }
}
