<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Type;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class TypeInLuigisBoxEnum extends AbstractEnum
{
    public const string ARTICLE = 'article';
    public const string BRAND = 'brand';
    public const string CATEGORY = 'category';
    public const string PRODUCT = 'item';
}
