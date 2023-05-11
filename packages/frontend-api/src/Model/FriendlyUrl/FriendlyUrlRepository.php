<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\FriendlyUrl;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository as FrameworkFriendlyUrlRepository;

class FriendlyUrlRepository
{
    protected FrameworkFriendlyUrlRepository $friendlyUrlRepository;

    protected EntityManagerInterface $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        FrameworkFriendlyUrlRepository $friendlyUrlRepository
    ) {
        $this->friendlyUrlRepository = $friendlyUrlRepository;
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getFriendlyUrlRepository(): EntityRepository
    {
        return $this->em->getRepository(FriendlyUrl::class);
    }

    /**
     * @param int $domainId
     * @param string $routeName
     * @param string $slug
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl|null
     */
    public function findFriendlyUrlBySlugAndRouteName(int $domainId, string $routeName, string $slug): ?FriendlyUrl
    {
        $criteria = [
            'domainId' => $domainId,
            'routeName' => $routeName,
            'slug' => $slug,
        ];

        return $this->getFriendlyUrlRepository()->findOneBy($criteria);
    }
}
