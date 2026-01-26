<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Image\Config\Resources;

use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImageFolder;

#[EntityImageFolder(name: 'childFolder')]
class TestEntityChildWithFolder extends TestEntityParentWithTypesAndFolder
{
}
