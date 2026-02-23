<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\FriendlyUrl;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository as FrameworkFriendlyUrlRepository;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlSlugNormalizer;

class FriendlyUrlRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly FrameworkFriendlyUrlRepository $friendlyUrlRepository,
    ) {
    }

    protected function getFriendlyUrlRepository(): EntityRepository
    {
        return $this->em->getRepository(FriendlyUrl::class);
    }

    public function findFriendlyUrlBySlugAndRouteName(int $domainId, string $routeName, string $slug): ?FriendlyUrl
    {
        $criteria = [
            'domainId' => $domainId,
            'routeName' => $routeName,
            'slug' => FriendlyUrlSlugNormalizer::normalize($slug),
        ];

        return $this->getFriendlyUrlRepository()->findOneBy($criteria);
    }
}
