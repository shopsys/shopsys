<?php

declare(strict_types=1);

namespace App\Model\Mail;

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
}
