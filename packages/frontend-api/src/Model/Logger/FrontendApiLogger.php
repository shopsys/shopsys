<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Logger;

use Overblog\GraphQLBundle\Validator\Exception\ArgumentsValidationException;
use Override;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Stringable;
use Symfony\Component\Validator\ConstraintViolation;

class FrontendApiLogger implements LoggerInterface
{
    public function __construct(
        protected readonly LoggerInterface $logger,
        protected readonly bool $isValidationLoggedAsError,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function log($level, string|Stringable $message, array $context = []): void
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
    #[Override]
    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function error(string|Stringable $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function info(string|Stringable $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }
}
