<?php

declare(strict_types=1);

namespace Shopsys\MakerBundle\EntityConfig;

enum EntityTypeEnum
{
    case ENTITY;
    case TRANSLATION;
    case DOMAIN;
}
