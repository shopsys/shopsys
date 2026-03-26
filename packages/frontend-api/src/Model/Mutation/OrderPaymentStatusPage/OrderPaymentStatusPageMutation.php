<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\OrderPaymentStatusPage;

use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\GoPay\GoPayClientFactory;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;

class OrderPaymentStatusPageMutation extends AbstractMutation
{
    public function __construct(
        protected readonly OrderApiFacade $orderApiFacade,
        protected readonly Domain $domain,
        protected readonly GoPayClientFactory $goPayClientFactory,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * Resets the validity hash server-side (instead of accepting it from the client)
     * and returns both the hash and the GoPay embed JS URL in one response.
     *
     * This replaces the original design where the frontend sent its own hash —
     * server-generated hash is more secure and reduces the number of API calls
     * (frontend previously needed a separate call to get the embed JS URL).
     *
     * @return array{goPayEmbedJs: string, orderPaymentStatusPageValidityHash: string}
     */
    public function setOrderPaymentStatusPageValidityHashMutation(Argument $argument): array
    {
        $uuid = $argument['orderUuid'];
        $order = $this->orderApiFacade->getByUuid($uuid);

        $domainConfig = $this->domain->getDomainConfigById($order->getDomainId());
        $goPayClient = $this->goPayClientFactory->createByDomain($domainConfig);

        $order->resetOrderPaymentStatusPageValidityHash();
        $this->em->flush();

        return [
            'goPayEmbedJs' => $goPayClient->urlToEmbedJs(),
            'orderPaymentStatusPageValidityHash' => $order->getOrderPaymentStatusPageValidityHash(),
        ];
    }
}
