<?php

declare(strict_types=1);

namespace App\Model\Mail;

use App\Model\Payment\Payment;
use App\Model\Transport\Transport;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateRepository as BaseMailTemplateRepository;

/**
 * @method \App\Model\Mail\MailTemplate|null findByNameAndDomainId(string $templateName, int $domainId)
 * @method \App\Model\Mail\MailTemplate getByNameAndDomainId(string $templateName, int $domainId)
 * @method \App\Model\Mail\MailTemplate[] getAllByDomainId(int $domainId)
 * @method \App\Model\Mail\MailTemplate getById(int $mailTemplateId)
 */
class MailTemplateRepository extends BaseMailTemplateRepository
{
    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createGridQueryBuilder(int $domainId): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder($domainId);
        $queryBuilder
            ->addSelect('tt.name as transportName, pt.name as paymentName')
            ->leftJoin('mt.transport', 't')
            ->leftJoin('t.translations', 'tt', Join::WITH, 'tt.locale = :locale')
            ->leftJoin('mt.payment', 'p')
            ->leftJoin('p.translations', 'pt', Join::WITH, 'pt.locale = :locale')
            ->setParameter('locale', 'cs');

        return $queryBuilder;
    }

    /**
     * @param int $domainId
     * @param \App\Model\Transport\Transport $transport
     * @param \App\Model\Payment\Payment $payment
     * @param string $orderStockStatus
     * @return \App\Model\Mail\MailTemplate|null
     */
    public function findOrderStockStatusMailTemplate(
        int $domainId,
        Transport $transport,
        Payment $payment,
        string $orderStockStatus
    ): ?MailTemplate {
        /** @var \App\Model\Mail\MailTemplate $mailTemplate */
        $mailTemplate = $this->getMailTemplateRepository()->findOneBy([
            'name' => MailTemplate::ORDER_STOCK_STATUS_NAME,
            'domainId' => $domainId,
            'transport' => $transport,
            'payment' => $payment,
            'orderStockStatus' => $orderStockStatus,
        ]);

        return $mailTemplate;
    }
}
