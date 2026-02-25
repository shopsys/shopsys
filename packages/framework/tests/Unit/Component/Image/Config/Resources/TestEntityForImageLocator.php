<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Image\Config\Resources;

use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage;
use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImageFolder;

#[EntityImageFolder(name: 'Name_1')]
#[EntityImage(name: 'TypeName_1')]
#[EntityImage(name: 'TypeName_2')]
class TestEntityForImageLocator
{
}
