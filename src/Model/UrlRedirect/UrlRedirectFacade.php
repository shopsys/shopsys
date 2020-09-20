<?php

declare(strict_types=1);


namespace App\Model\UrlRedirect;

use Doctrine\ORM\EntityManagerInterface;

class UrlRedirectFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var \App\Model\UrlRedirect\UrlRedirectRepository
     */
    private UrlRedirectRepository $urlRedirectRepository;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\UrlRedirect\UrlRedirectRepository $urlRedirectRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        UrlRedirectRepository $urlRedirectRepository
    ) {
        $this->em = $em;
        $this->urlRedirectRepository = $urlRedirectRepository;
    }

    /**
     * @param \App\Model\UrlRedirect\UrlRedirectData $urlRedirectData
     * @return \App\Model\UrlRedirect\UrlRedirect
     */
    public function create(UrlRedirectData $urlRedirectData): UrlRedirect
    {
        $urlRedirect = new UrlRedirect($urlRedirectData);

        $this->em->persist($urlRedirect);
        $this->em->flush();

        return $urlRedirect;
    }

    /**
     * @param string $oldUrl
     * @param int $domainId
     * @return \App\Model\UrlRedirect\UrlRedirect|null
     */
    public function findByOldUrlAndDomainId(string $oldUrl, int $domainId): ?UrlRedirect
    {
        return $this->urlRedirectRepository->findByOldUrlAndDomainId($oldUrl, $domainId);
    }
}
