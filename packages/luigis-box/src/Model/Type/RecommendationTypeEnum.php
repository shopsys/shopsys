<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Type;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class RecommendationTypeEnum extends AbstractEnum
{
    public const string BASKET = 'basket';
    public const string BASKET_POPUP = 'basket_popup';
    public const string CATEGORY = 'category';
    public const string ITEM_DETAIL = 'item_detail';
    public const string PERSONALIZED = 'personalized';
}
