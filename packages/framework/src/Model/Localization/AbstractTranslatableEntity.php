<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Localization;

use Prezent\Doctrine\Translatable\Entity\AbstractTranslatable;

abstract class AbstractTranslatableEntity extends AbstractTranslatable
{
    use TranslatableEntityTrait;
}
