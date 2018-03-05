<?php

namespace Shopsys\FrameworkBundle\Model\Feed;

use Shopsys\ProductFeed\FeedConfigInterface;

class FeedConfigRegistry
{
    const TYPE_STANDARD = 'standard';
    const TYPE_DELIVERY = 'delivery';

    const KNOWN_TYPES = [
        self::TYPE_STANDARD,
        self::TYPE_DELIVERY,
    ];

    /**
     * @var \Shopsys\ProductFeed\FeedConfigInterface[][]
     */
    private $feedConfigsByType;

    public function __construct()
    {
        $this->feedConfigsByType = [];
    }

    /**
     * @param \Shopsys\ProductFeed\FeedConfigInterface $feedConfig
     * @param string $type
     */
    public function registerFeedConfig(FeedConfigInterface $feedConfig, $type = self::TYPE_STANDARD)
    {
        self::assertTypeIsKnown($type);

        $this->feedConfigsByType[$type][] = $feedConfig;
    }

    /**
     * @param string $type
     * @return \Shopsys\ProductFeed\FeedConfigInterface[]
     */
    public function getFeedConfigsByType($type)
    {
        return $this->feedConfigsByType[$type] ?: [];
    }

    /**
     * @param string $feedName
     * @return \Shopsys\ProductFeed\FeedConfigInterface
     */
    public function getFeedConfigByName($feedName)
    {
        foreach ($this->getAllFeedConfigs() as $feedConfig) {
            if ($feedConfig->getFeedName() === $feedName) {
                return $feedConfig;
            }
        }

        throw new \Shopsys\FrameworkBundle\Model\Feed\Exception\FeedConfigNotFoundException($feedName);
    }

    /**
     * @return \Shopsys\ProductFeed\FeedConfigInterface[]
     */
    public function getAllFeedConfigs()
    {
        $allFeedConfigs = [];
        foreach ($this->feedConfigsByType as $feedConfigs) {
            $allFeedConfigs = array_merge($allFeedConfigs, $feedConfigs);
        }

        return $allFeedConfigs;
    }

    /**
     * @param string $type
     */
    public static function assertTypeIsKnown($type)
    {
        if (!in_array($type, self::KNOWN_TYPES, true)) {
            throw new \Shopsys\FrameworkBundle\Model\Feed\Exception\UnknownFeedConfigTypeException($type, self::KNOWN_TYPES);
        }
    }
}
