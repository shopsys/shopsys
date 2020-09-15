<?php

declare(strict_types=1);


namespace App\Model\UrlRedirect;

use App\Model\Stock\Stock;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class UrlRedirectRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $em;

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
    private function getRepository(): EntityRepository
    {
        return $this->em->getRepository(UrlRedirect::class);
    }

    /**
     * @param string $oldUrl
     * @param int $domainId
     * @return \App\Model\UrlRedirect\UrlRedirect|null
     */
    public function findByOldUrlAndDomainId(string $oldUrl, int $domainId): ?UrlRedirect
    {
        return $this->getRepository()->findOneBy(['oldUrl' => $oldUrl, 'domainId' => $domainId]);
    }
}
