<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Component\String\HashGenerator;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderHashGenerateException;

class OrderHashGeneratorRepository
{
    protected const HASH_LENGTH = 50;
    protected const MAX_GENERATE_TRIES = 100;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderRepository $orderRepository
     * @param \Shopsys\FrameworkBundle\Component\String\HashGenerator $hashGenerator
     */
    public function __construct(
        protected readonly OrderRepository $orderRepository,
        protected readonly HashGenerator $hashGenerator,
    ) {
    }

    /**
     * @return string
     */
    public function getUniqueHash()
    {
        $triesCount = 0;

        do {
            $hash = $this->hashGenerator->generateHash(static::HASH_LENGTH);
            $order = $this->orderRepository->findByUrlHashIncludingDeletedOrders($hash);
            $triesCount++;

            if ($triesCount > static::MAX_GENERATE_TRIES) {
                throw new OrderHashGenerateException('Trying generate hash reached the limit.');
            }
        } while ($order !== null);

        return $hash;
    }
}
