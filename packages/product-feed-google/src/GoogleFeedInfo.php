<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\GoogleBundle;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class GoogleFeedInfo implements FeedInfoInterface
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    #[Override]
    public function getLabel(): string
    {
        return 'Google';
    }

    #[Override]
    public function getName(): string
    {
        return 'google';
    }

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
