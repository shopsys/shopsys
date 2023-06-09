<?php

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Component\String\HashGenerator;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderHashGenerateException;

class OrderHashGeneratorRepository
{
    protected const HASH_LENGTH = 50;
    protected const MAX_GENERATE_TRIES = 100;

    protected OrderRepository $orderRepository;

    protected HashGenerator $hashGenerator;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderRepository $orderRepository
     * @param \Shopsys\FrameworkBundle\Component\String\HashGenerator $hashGenerator
     */
    public function __construct(
        OrderRepository $orderRepository,
        HashGenerator $hashGenerator
    ) {
        $this->orderRepository = $orderRepository;
        $this->hashGenerator = $hashGenerator;
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
