<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImageEntityConfigNotFoundException extends NotFoundHttpException
{
    public function __construct(protected string $entityClassOrName, ?Exception $previous = null)
    {
        parent::__construct('Not found image config for entity "' . $entityClassOrName . '".', $previous);
    }

    public function getEntityClassOrName(): string
    {
        return $this->entityClassOrName;
    }
}
