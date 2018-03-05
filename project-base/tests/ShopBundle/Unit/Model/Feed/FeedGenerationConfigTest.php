<?php

namespace Tests\ShopBundle\Unit\Model\Feed;

use PHPUnit_Framework_TestCase;
use Shopsys\FrameworkBundle\Model\Feed\FeedGenerationConfig;

class FeedGenerationConfigTest extends PHPUnit_Framework_TestCase
{
    public function isSameFeedAndDomainProvider()
    {
        return [
            [new FeedGenerationConfig('feedName', 1), true],
            [new FeedGenerationConfig('feedName2', 1), false],
            [new FeedGenerationConfig('feedName', 2), false],
            [new FeedGenerationConfig('feedName2', 2), false],
        ];
    }

    /**
     * @dataProvider isSameFeedAndDomainProvider
     */
    public function testIsSameFeedAndDomain($feedGenerationConfigToComapareWith, $expectedResult)
    {
        $feedGenerationConfig = new FeedGenerationConfig('feedName', 1);

        $this->assertSame($expectedResult, $feedGenerationConfig->isSameFeedAndDomain($feedGenerationConfigToComapareWith));
    }
}
