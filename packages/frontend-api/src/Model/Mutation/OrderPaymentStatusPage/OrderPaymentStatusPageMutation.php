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

    public function setOrderPaymentStatusPageValidityHashMutation(Argument $argument): string
    {
        $uuid = $argument['orderUuid'];
        $order = $this->orderApiFacade->getByUuid($uuid);
        $orderPaymentStatusPageValidityHash = $argument['orderPaymentStatusPageValidityHash'];

        $domainConfig = $this->domain->getDomainConfigById($order->getDomainId());
        $goPayClient = $this->goPayClientFactory->createByDomain($domainConfig);

        $order->setOrderPaymentStatusPageValidityHash($orderPaymentStatusPageValidityHash);
        $this->em->flush();

        return $goPayClient->urlToEmbedJs();
    }
}
