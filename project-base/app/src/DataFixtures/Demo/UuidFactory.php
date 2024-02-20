<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Ramsey\Uuid\UuidFactory as BaseUuidFactory;
use Ramsey\Uuid\UuidInterface;

class UuidFactory extends BaseUuidFactory
{
    private int $counter = 0;

    #[\Override]
    public function uuid4(): UuidInterface
    {
        $this->counter++;

        return $this->uuid(str_pad((string)$this->counter, 16, '0', STR_PAD_LEFT));
    }
}
