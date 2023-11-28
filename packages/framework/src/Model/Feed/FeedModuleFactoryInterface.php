<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

interface FeedModuleFactoryInterface
{
    /**
     * @param string $name
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedModule
     */
    public function create(string $name, int $domainId): FeedModule;
}
