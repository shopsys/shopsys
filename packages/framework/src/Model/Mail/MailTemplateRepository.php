<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Mail\Exception\MailTemplateNotFoundException;

class MailTemplateRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly Localization $localization,
    ) {
    }

    protected function getMailTemplateRepository(): EntityRepository
    {
        return $this->em->getRepository(MailTemplate::class);
    }

    public function findByNameAndDomainId(string $templateName, int $domainId): ?MailTemplate
    {
        $criteria = [
            'name' => $templateName,
            'domainId' => $domainId,
        ];

        return $this->getMailTemplateRepository()->findOneBy($criteria);
    }

    public function getByNameAndDomainId(string $templateName, int $domainId): MailTemplate
    {
        $mailTemplate = $this->findByNameAndDomainId($templateName, $domainId);

        if ($mailTemplate === null) {
            $message = 'Email template with name "' . $templateName . '" was not found on domain with ID ' . $domainId . '.';

            throw new MailTemplateNotFoundException($message);
        }

        return $mailTemplate;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplate[]
     */
    public function getAllByDomainId(int $domainId): array
    {
        $criteria = ['domainId' => $domainId];

        return $this->getMailTemplateRepository()->findBy($criteria);
    }

    public function createQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->getMailTemplateRepository()->createQueryBuilder('mt')
            ->where('mt.domainId = :domainId')
            ->setParameter('domainId', $domainId);
    }

    public function getById(int $mailTemplateId): MailTemplate
    {
        $mailTemplate = $this->getMailTemplateRepository()->find($mailTemplateId);

        if ($mailTemplate === null) {
            throw new MailTemplateNotFoundException('Email template with ID ' . $mailTemplateId . ' not found.');
        }

        return $mailTemplate;
    }

    public function existsTemplateWithEnabledSendingHavingEmptyBodyOrSubject(): bool
    {
        $countOfEmptyTemplates = (int)$this->em->createQueryBuilder()
            ->select('COUNT(mt)')
            ->from(MailTemplate::class, 'mt')
            ->where('mt.sendMail = TRUE')
            ->andWhere('mt.body IS NULL OR mt.subject IS NULL')
            ->getQuery()
            ->getSingleScalarResult();

        return $countOfEmptyTemplates > 0;
    }

    public function createGridQueryBuilder(int $domainId): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder($domainId);
        $queryBuilder
            ->addSelect('ost.name as orderStatusName')
            ->leftJoin('mt.orderStatus', 'os')
            ->leftJoin('os.translations', 'ost', Join::WITH, 'ost.locale = :locale')
            ->addSelect('cst.name as complaintStatusName')
            ->leftJoin('mt.complaintStatus', 'cs')
            ->leftJoin('cs.translations', 'cst', Join::WITH, 'cst.locale = :locale')
            ->setParameter('locale', $this->localization->getCurrentLocaleForTranslatableEntities());

        return $queryBuilder;
    }
}
