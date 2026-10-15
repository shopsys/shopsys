<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Media;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class MediaFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(MediaData $data): Media
    {
        $entityClassName = $this->entityNameResolver->resolve(Media::class);

        return new $entityClassName($data);
    }
}
