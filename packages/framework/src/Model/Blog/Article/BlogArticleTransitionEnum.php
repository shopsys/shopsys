<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class BlogArticleTransitionEnum extends AbstractEnum
{
    public const string TRANSITION_TO_DRAFT = 'to_draft';
    public const string TRANSITION_TO_PREVIEW = 'to_preview';
    public const string TRANSITION_PUBLISH = 'publish';
}
