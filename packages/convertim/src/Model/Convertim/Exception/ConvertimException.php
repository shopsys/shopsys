<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Convertim\Exception;

use Exception;
use Throwable;

class ConvertimException extends Exception
{
    /**
     * @param string $message
     * @param array<string, mixed> $context
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $message = '',
        protected array $context = [],
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
