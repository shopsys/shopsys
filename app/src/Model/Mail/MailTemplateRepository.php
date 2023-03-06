<?php

declare(strict_types=1);

namespace App\Model\Mail;

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
            ->addSelect('ost.name as orderStatusName')
            ->leftJoin('mt.orderStatus', 'os')
            ->leftJoin('os.translations', 'ost', Join::WITH, 'ost.locale = :locale')
            ->setParameter('locale', $this->localization->getAdminLocale());

        return $queryBuilder;
    }

    /**
     * @param int $domainId
     * @param \App\Model\Order\Status\OrderStatus $orderStatus
     * @return \App\Model\Mail\MailTemplate|null
     */
    public function findOrderStatusMailTemplate(
        int $domainId,
        OrderStatus $orderStatus
    ): ?MailTemplate {
        /** @var \App\Model\Mail\MailTemplate $mailTemplate */
        $mailTemplate = $this->getMailTemplateRepository()->findOneBy([
            'name' => MailTemplate::ORDER_STATUS_NAME,
            'domainId' => $domainId,
            'orderStatus' => $orderStatus,
        ]);

        return $mailTemplate;
    }
}
