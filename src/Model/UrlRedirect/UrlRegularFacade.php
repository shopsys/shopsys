<?php

declare(strict_types=1);


namespace App\Model\UrlRedirect;

use Doctrine\ORM\EntityManagerInterface;

class UrlRegularFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var \App\Model\UrlRedirect\UrlRegularRepository
     */
    private UrlRegularRepository $urlRegularRepository;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\UrlRedirect\UrlRegularRepository $urlRegularRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        UrlRegularRepository $urlRegularRepository
    ) {
        $this->em = $em;
        $this->urlRegularRepository = $urlRegularRepository;
    }

    /**
     * @param string $regular
     * @param string $newUrl
     * @param int $domainId
     * @return \App\Model\UrlRedirect\UrlRegular
     */
    public function create(string $regular, string $newUrl, int $domainId)
    {
        $urlRegular = new UrlRegular($regular, $newUrl, $domainId);

        $this->em->persist($urlRegular);
        $this->em->flush();

        return $urlRegular;
    }

    /**
     * @param string $regular
     * @param int $domainId
     * @return \App\Model\UrlRedirect\UrlRegular|null
     */
    public function findByRegularAndDomainId(string $regular, int $domainId): ?UrlRegular
    {
        return $this->urlRegularRepository->findByRegularAndDomainId($regular, $domainId);
    }

    /**
     * @param int $domainId
     * @return string[][]
     */
    public function getAllByDomainId(int $domainId): array
    {
        return $this->urlRegularRepository->getAllByDomainId($domainId);
    }
}
