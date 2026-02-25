<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Image\Config\Resources;

use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage;

#[EntityImage(name: 'thumbnail')]
#[EntityImage(name: 'banner', multiple: true)]
class TestEntityChildWithTypes extends TestEntityParentWithTypesAndFolder
{
}
