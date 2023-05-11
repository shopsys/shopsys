<?php

namespace Shopsys\FrameworkBundle\Model\Feed;

use Shopsys\FrameworkBundle\Component\Utils\Utils;
use Shopsys\FrameworkBundle\Model\Feed\Exception\FeedNameNotUniqueException;
use Shopsys\FrameworkBundle\Model\Feed\Exception\FeedNotFoundException;
use Shopsys\FrameworkBundle\Model\Feed\Exception\UnknownFeedTypeException;

class FeedRegistry
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Feed\FeedInterface[][]
     */
    protected array $feedsByType = [];

    /**
     * @var \Shopsys\FrameworkBundle\Model\Feed\FeedInterface[]
     */
    protected array $feedsByName = [];

    /**
     * @var string[]
     */
    protected array $knownTypes;

    protected string $defaultType;

    /**
     * @param string[] $knownTypes
     * @param string $defaultType
     */
    public function __construct(array $knownTypes, string $defaultType)
    {
        foreach ($knownTypes as $type) {
            $this->feedsByType[$type] = [];
        }
        $this->knownTypes = $knownTypes;
        $this->defaultType = $defaultType;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedInterface $feed
     * @param string|null $type
     */
    public function registerFeed(FeedInterface $feed, ?string $type = null): void
    {
        $type = Utils::ifNull($type, $this->defaultType);
        $this->assertTypeIsKnown($type);

        $name = $feed->getInfo()->getName();
        $this->assertNameIsUnique($name);

        $this->feedsByType[$type][$name] = $feed;
        $this->feedsByName[$name] = $feed;
    }

    /**
     * @param string $type
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedInterface[]
     */
    public function getFeeds(string $type): array
    {
        $this->assertTypeIsKnown($type);

        return $this->feedsByType[$type];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedInterface[]
     */
    public function getAllFeeds(): array
    {
        return $this->feedsByName;
    }

    /**
     * @param string $name
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedInterface
     */
    public function getFeedByName(string $name): FeedInterface
    {
        if (!array_key_exists($name, $this->feedsByName)) {
            throw new FeedNotFoundException($name);
        }

        return $this->feedsByName[$name];
    }

    /**
     * @param string $type
     */
    protected function assertTypeIsKnown(string $type): void
    {
        if (!in_array($type, $this->knownTypes, true)) {
            throw new UnknownFeedTypeException($type, $this->knownTypes);
        }
    }

    /**
     * @param string $name
     */
    protected function assertNameIsUnique(string $name): void
    {
        if (array_key_exists($name, $this->feedsByName)) {
            throw new FeedNameNotUniqueException($name);
        }
    }
}
