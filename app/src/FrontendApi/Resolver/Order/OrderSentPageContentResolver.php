<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Order;

use App\FrontendApi\Model\Order\OrderFacade;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

final class OrderSentPageContentResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \App\FrontendApi\Model\Order\OrderFacade
     */
    private OrderFacade $orderFacade;

    /**
     * @param \App\FrontendApi\Model\Order\OrderFacade $orderFacade
     */
    public function __construct(OrderFacade $orderFacade)
    {
        $this->orderFacade = $orderFacade;
    }

    /**
     * @param string $orderUuid
     * @return string
     */
    public function resolve(string $orderUuid): string
    {
        return $this->orderFacade->getOrderSentPageContent($orderUuid);
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolve' => 'orderSentPageContent'];
    }
}
