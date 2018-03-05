<?php

namespace Shopsys\FrameworkBundle\Model\Feed;

use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class FeedCronModule implements IteratedCronModuleInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Feed\FeedFacade
     */
    private $feedFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Feed\FeedGenerationConfig|null
     */
    private $feedGenerationConfigToContinue;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    private $setting;

    public function __construct(FeedFacade $feedFacade, Setting $setting)
    {
        $this->feedFacade = $feedFacade;
        $this->setting = $setting;
    }

    /**
     * @inheritdoc
     */
    public function setLogger(Logger $logger)
    {
    }

    /**
     * @inheritdoc
     */
    public function iterate()
    {
        if ($this->feedGenerationConfigToContinue === null) {
            $this->feedGenerationConfigToContinue = $this->feedFacade->getFirstFeedGenerationConfig();
        }
        $this->feedGenerationConfigToContinue = $this->feedFacade->generateStandardFeedsIteratively(
            $this->feedGenerationConfigToContinue
        );

        return $this->feedGenerationConfigToContinue !== null;
    }

    /**
     * @inheritdoc
     */
    public function sleep()
    {
        $this->setting->set(
            Setting::FEED_NAME_TO_CONTINUE,
            $this->feedGenerationConfigToContinue->getFeedName()
        );
        $this->setting->set(
            Setting::FEED_DOMAIN_ID_TO_CONTINUE,
            $this->feedGenerationConfigToContinue->getDomainId()
        );
        $this->setting->set(
            Setting::FEED_ITEM_ID_TO_CONTINUE,
            $this->feedGenerationConfigToContinue->getFeedItemId()
        );
    }

    /**
     * @inheritdoc
     */
    public function wakeUp()
    {
        $this->feedGenerationConfigToContinue = new FeedGenerationConfig(
            $this->setting->get(Setting::FEED_NAME_TO_CONTINUE),
            $this->setting->get(Setting::FEED_DOMAIN_ID_TO_CONTINUE),
            $this->setting->get(Setting::FEED_ITEM_ID_TO_CONTINUE)
        );
    }
}
