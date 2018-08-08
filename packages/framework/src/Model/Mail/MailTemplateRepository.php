<?php

namespace Shopsys\FrameworkBundle\Model\Mail;

use Doctrine\ORM\EntityManagerInterface;

class MailTemplateRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function getMailTemplateRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(MailTemplate::class);
    }
    
    public function findByNameAndDomainId(string $templateName, int $domainId): ?\Shopsys\FrameworkBundle\Model\Mail\MailTemplate
    {
        $criteria = ['name' => $templateName, 'domainId' => $domainId];

        return $this->getMailTemplateRepository()->findOneBy($criteria);
    }
    
    public function getByNameAndDomainId(string $templateName, int $domainId): \Shopsys\FrameworkBundle\Model\Mail\MailTemplate
    {
        $mailTemplate = $this->findByNameAndDomainId($templateName, $domainId);
        if ($mailTemplate === null) {
            $message = 'E-mail template with name "' . $templateName . '" was not found on domain with ID ' . $domainId . '.';
            throw new \Shopsys\FrameworkBundle\Model\Mail\Exception\MailTemplateNotFoundException($message);
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
}
