<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\Feed\Exception\FeedNotFoundException;

class FeedModuleRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedModuleFactoryInterface $feedModuleFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly FeedModuleFactoryInterface $feedModuleFactory,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getFeedModuleRepository(): EntityRepository
    {
        return $this->em->getRepository(FeedModule::class);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedConfig $feedConfig
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedModule[]
     */
    public function getFeedModulesByConfigIndexedByDomainId(FeedConfig $feedConfig): array
    {
        $feedName = $feedConfig->getFeed()->getInfo()->getName();
        $feedModules = [];

        foreach ($feedConfig->getDomainIds() as $domainId) {
            $feedModule = $this->getFeedModuleRepository()->findOneBy([
                'name' => $feedName,
                'domainId' => $domainId,
            ]);

            if ($feedModule !== null) {
                $feedModules[$domainId] = $feedModule;

                continue;
            }

            $feedModule = $this->feedModuleFactory->create($feedName, $domainId);
            $this->em->persist($feedModule);

            $feedModules[$domainId] = $feedModule;
        }

        $this->em->flush();

        return $feedModules;
    }

    /**
     * @param string $name
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedModule
     */
    public function getFeedModuleByNameAndDomainId(string $name, int $domainId): FeedModule
    {
        $feedModule = $this->getFeedModuleRepository()->findOneBy([
            'name' => $name,
            'domainId' => $domainId,
        ]);

        if ($feedModule === null) {
            throw new FeedNotFoundException($name, $domainId);
        }

        return $feedModule;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedModule[]
     */
    public function getAllScheduledFeedModules(): array
    {
        return $this->getFeedModuleRepository()->findBy(['scheduled' => true]);
    }

    /**
     * @param string $feedName
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedModule[]
     */
    public function findFeedModulesByName(string $feedName): array
    {
        return $this->getFeedModuleRepository()->findBy([
            'name' => $feedName,
        ]);
    }
}
