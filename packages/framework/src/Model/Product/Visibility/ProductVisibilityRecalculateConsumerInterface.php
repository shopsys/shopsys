<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Visibility;

use PhpAmqpLib\Message\AMQPMessage;

interface ProductVisibilityRecalculateConsumerInterface
{
    /**
     * @param \PhpAmqpLib\Message\AMQPMessage $message
     * @return int
     */
    public function execute(AMQPMessage $message);
}
