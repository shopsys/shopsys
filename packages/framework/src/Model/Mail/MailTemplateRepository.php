<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Mail\Exception\MailTemplateNotFoundException;

class MailTemplateRepository
{
    protected EntityManagerInterface $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getMailTemplateRepository()
    {
        return $this->em->getRepository(MailTemplate::class);
    }

    /**
     * @param string $templateName
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplate|null
     */
    public function findByNameAndDomainId($templateName, $domainId)
    {
        $criteria = [
            'name' => $templateName,
            'domainId' => $domainId,
        ];

        return $this->getMailTemplateRepository()->findOneBy($criteria);
    }

    /**
     * @param string $templateName
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplate
     */
    public function getByNameAndDomainId($templateName, $domainId)
    {
        $mailTemplate = $this->findByNameAndDomainId($templateName, $domainId);

        if ($mailTemplate === null) {
            $message = 'Email template with name "' . $templateName . '" was not found on domain with ID ' . $domainId . '.';

            throw new MailTemplateNotFoundException($message);
        }

        return $mailTemplate;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplate[]
     */
    public function getAllByDomainId($domainId)
    {
        $criteria = ['domainId' => $domainId];

        return $this->getMailTemplateRepository()->findBy($criteria);
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->getMailTemplateRepository()->createQueryBuilder('mt')
            ->where('mt.domainId = :domainId')
            ->setParameter('domainId', $domainId);
    }

    /**
     * @param int $mailTemplateId
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplate
     */
    public function getById(int $mailTemplateId): MailTemplate
    {
        $mailTemplate = $this->getMailTemplateRepository()->find($mailTemplateId);

        if ($mailTemplate === null) {
            throw new MailTemplateNotFoundException('Email template with ID ' . $mailTemplateId . ' not found.');
        }

        return $mailTemplate;
    }

    /**
     * @return bool
     */
    public function existsTemplateWithEnabledSendingHavingEmptyBodyOrSubject()
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
}
