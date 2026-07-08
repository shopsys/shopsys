<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\TransportAndPayment;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

class FreeTransportAndPaymentPriceLimitRepository
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(FreeTransportAndPaymentPriceLimit::class);
    }

    public function findByDomainIdAndCurrency(int $domainId, Currency $currency): ?FreeTransportAndPaymentPriceLimit
    {
        return $this->getRepository()->findOneBy([
            'domainId' => $domainId,
            'currency' => $currency,
        ]);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentPriceLimit[]
     */
    public function getAllByDomainId(int $domainId): array
    {
        return $this->getRepository()->findBy(['domainId' => $domainId]);
    }
}
