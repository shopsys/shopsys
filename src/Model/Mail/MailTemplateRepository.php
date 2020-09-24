<?php

declare(strict_types=1);

namespace App\Model\Mail;

use App\Model\Payment\Payment;
use App\Model\Transport\Transport;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateRepository as BaseMailTemplateRepository;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;

/**
 * @method \App\Model\Mail\MailTemplate|null findByNameAndDomainId(string $templateName, int $domainId)
 * @method \App\Model\Mail\MailTemplate getByNameAndDomainId(string $templateName, int $domainId)
 * @method \App\Model\Mail\MailTemplate[] getAllByDomainId(int $domainId)
 * @method \App\Model\Mail\MailTemplate getById(int $mailTemplateId)
 */
class MailTemplateRepository extends BaseMailTemplateRepository
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    private Localization $localization;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        EntityManagerInterface $em,
        Localization $localization
    ) {
        parent::__construct($em);
        $this->localization = $localization;
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createGridQueryBuilder(int $domainId): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder($domainId);
        $queryBuilder
            ->addSelect('tt.name as transportName, pt.name as paymentName, ost.name as orderStatusName')
            ->leftJoin('mt.transport', 't')
            ->leftJoin('t.translations', 'tt', Join::WITH, 'tt.locale = :locale')
            ->leftJoin('mt.payment', 'p')
            ->leftJoin('p.translations', 'pt', Join::WITH, 'pt.locale = :locale')
            ->leftJoin('mt.orderStatus', 'os')
            ->leftJoin('os.translations', 'ost', Join::WITH, 'ost.locale = :locale')
            ->setParameter('locale', $this->localization->getAdminLocale());

        return $queryBuilder;
    }

    /**
     * @param int $domainId
     * @param \App\Model\Transport\Transport $transport
     * @param \App\Model\Payment\Payment $payment
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus $orderStatus
     * @return \App\Model\Mail\MailTemplate|null
     */
    public function findOrderStatusMailTemplate(
        int $domainId,
        Transport $transport,
        Payment $payment,
        OrderStatus $orderStatus
    ): ?MailTemplate {
        /** @var \App\Model\Mail\MailTemplate $mailTemplate */
        $mailTemplate = $this->getMailTemplateRepository()->findOneBy([
            'name' => MailTemplate::ORDER_STATUS_NAME,
            'domainId' => $domainId,
            'transport' => $transport,
            'payment' => $payment,
            'orderStatus' => $orderStatus,
        ]);

        return $mailTemplate;
    }
}
