<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Error;

class ErrorIdProvider
{
    /**
     * @var string|null
     */
    protected $errorId;

    /**
     * @param \Throwable $exception
     */
    public function setErrorId(\Throwable $exception): void
    {
        while ($exception) {
            $data[] = [
                get_class($exception), $exception->getMessage(), $exception->getCode(), $exception->getFile(), $exception->getLine(),
                array_map(function (array $item): array {
                    unset($item['args']);
                    return $item;
                }, $exception->getTrace()),
            ];
            $exception = $exception->getPrevious();
        }

        $this->errorId = substr(md5(serialize($data)), 0, 10);
    }

    /**
     * @return string
     */
    public function getErrorId(): string
    {
        return $this->errorId;
    }
}
