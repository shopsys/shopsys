<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\GoogleBundle;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class GoogleFeedInfo implements FeedInfoInterface
{
    public function __construct(protected readonly TranslatorInterface $translator)
    {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getLabel(): string
    {
        return 'Google';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getName(): string
    {
        return 'google';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getAdditionalInformation(): string
    {
        return $this->translator->trans(
            'Google Shopping product feed is not optimized for selling to Australia,
            Czechia, France, Germany, Italy, Netherlands, Spain, Switzerland, the UK,
            and the US. It is caused by missing \'shipping\' attribute.',
        );
    }
}
