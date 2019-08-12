<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search\Export;

use PhpAmqpLib\Message\AMQPMessage;

interface ProductSearchExportConsumerInterface
{
    /**
     * @param \PhpAmqpLib\Message\AMQPMessage $message
     * @return int
     */
    public function execute(AMQPMessage $message): int;
}
