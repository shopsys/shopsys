<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Maker\EntityConfig;

enum PropertyTargetEnum
{
    case ENTITY;
    case TRANSLATION;
    case DOMAIN;
}
