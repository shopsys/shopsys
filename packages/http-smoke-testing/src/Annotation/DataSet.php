<?php

declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class DataSet
{
    public int $statusCode = 200;

    /**
     * @var \Shopsys\HttpSmokeTesting\Annotation\Parameter[]
     */
    public array $parameters = [];
}
