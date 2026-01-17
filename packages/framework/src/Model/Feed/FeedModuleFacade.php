<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use Doctrine\ORM\EntityManagerInterface;

class FeedModuleFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly FeedModuleRepository $feedModuleRepository,
    ) {
    }

    public function deleteFeedCronModulesByName(string $feedName): void
    {
        $feedModules = $this->feedModuleRepository->findFeedModulesByName($feedName);

        foreach ($feedModules as $feedModule) {
            $this->em->remove($feedModule);
        }

        $this->em->flush();
    }
}
