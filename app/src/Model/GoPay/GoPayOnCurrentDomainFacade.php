<?php

declare(strict_types=1);

namespace App\Model\GoPay;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class GoPayOnCurrentDomainFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Model\GoPay\GoPayClientFactory
     */
    private $goPayClientFactory;

    /**
     * @param \App\Model\GoPay\GoPayClientFactory $goPayClientFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        GoPayClientFactory $goPayClientFactory,
        Domain $domain
    ) {
        $this->domain = $domain;
        $this->goPayClientFactory = $goPayClientFactory;
    }

    /**
     * @param \App\Model\GoPay\GoPayTransaction[] $goPayTransactions
     * @param int $domainId
     * @return \App\Model\GoPay\GoPayResponseData[]
     */
    public function getPaymentStatusesResponseDataByGoPayTransactionAndDomainId(array $goPayTransactions, int $domainId): array
    {
        $responses = [];
        $domainConfig = $this->domain->getDomainConfigById($domainId);
        $goPayClient = $this->goPayClientFactory->createByLocale($domainConfig->getLocale());

        foreach ($goPayTransactions as $goPayTransaction) {
            $responses[] = new GoPayResponseData(
                $goPayClient->getStatus($goPayTransaction->getGoPayId()),
                $goPayTransaction
            );
        }

        return $responses;
    }
}
