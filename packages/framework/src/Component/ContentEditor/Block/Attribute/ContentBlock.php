<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute;

use Attribute;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;

#[Attribute(Attribute::TARGET_CLASS)]
final class ContentBlock
{
    /**
     * @param string[] $profiles
     */
    public function __construct(
        public string $type,
        public array $profiles = [ContentProfileEnum::WEB],
    ) {
    }
}
