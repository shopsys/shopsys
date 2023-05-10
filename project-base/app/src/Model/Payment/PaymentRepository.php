<?php

declare(strict_types=1);

namespace App\Model\Payment;

use App\Model\GoPay\PaymentMethod\GoPayPaymentMethod;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentNotFoundException;
use Shopsys\FrameworkBundle\Model\Payment\Payment as BasePayment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentDomain;
use Shopsys\FrameworkBundle\Model\Payment\PaymentRepository as BasePaymentRepository;

/**
 * @method \App\Model\Payment\Payment[] getAll()
 * @method \App\Model\Payment\Payment[] getAllIncludingDeleted()
 * @method \App\Model\Payment\Payment|null findById(int $id)
 * @method \App\Model\Payment\Payment getById(int $id)
 * @method \App\Model\Payment\Payment[] getAllByTransport(\App\Model\Transport\Transport $transport)
 * @method \App\Model\Payment\Payment getOneByUuid(string $uuid)
 */
class PaymentRepository extends BasePaymentRepository
{
    /**
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethod $goPayPaymentMethod
     * @return \App\Model\Payment\Payment[]
     */
    public function getByGoPayPaymentMethod(GoPayPaymentMethod $goPayPaymentMethod): array
    {
        return $this->getPaymentRepository()->findBy(['goPayPaymentMethod' => $goPayPaymentMethod]);
    }

    /**
     * @param string $uuid
     * @param int $domainId
     * @return \App\Model\Payment\Payment
     */
    public function getEnabledOnDomainByUuid(string $uuid, int $domainId): BasePayment
    {
        $queryBuilder = $this->getPaymentRepository()->createQueryBuilder('p')
            ->join(PaymentDomain::class, 'pd', Join::WITH, 'p.id = pd.payment AND pd.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->where('p.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->andWhere('p.deleted = false')
            ->andWhere('pd.enabled = true')
            ->andWhere('p.hidden = false')
            ->andWhere('p.hiddenByGoPay = false');

        $payment = $queryBuilder->getQuery()->getOneOrNullResult();

        if ($payment === null) {
            throw new PaymentNotFoundException('Payment with UUID ' . $uuid . ' does not exist.');
        }

        return $payment;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\Payment\Payment[]
     */
    public function getAllWithEagerLoadedDomainsAndTranslations(DomainConfig $domainConfig): array
    {
        return $this->getQueryBuilderForAll()
            ->addSelect('pd')
            ->addSelect('pt')
            ->join('p.translations', 'pt', Join::WITH, 'pt.locale = :locale')
            ->join('p.domains', 'pd', Join::WITH, 'pd.domainId = :domainId')
            ->setParameter('locale', $domainConfig->getLocale())
            ->setParameter('domainId', $domainConfig->getId())
            ->getQuery()->execute();
    }
}
