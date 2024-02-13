<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use Doctrine\ORM\EntityManagerInterface;

class FeedModuleFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedModuleRepository $feedModuleRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly FeedModuleRepository $feedModuleRepository,
    ) {
    }

    /**
     * @param string $feedName
     */
    public function deleteFeedCronModulesByName(string $feedName): void
    {
        $feedModules = $this->feedModuleRepository->findFeedModulesByName($feedName);

        foreach ($feedModules as $feedModule) {
            $this->em->remove($feedModule);
        }

        $this->em->flush();
    }
}
