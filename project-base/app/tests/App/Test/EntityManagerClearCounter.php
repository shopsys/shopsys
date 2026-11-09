<?php

declare(strict_types=1);

namespace Tests\App\Test;

final class EntityManagerClearCounter
{
    private int $clearsCount = 0;

    public function onClear(): void
    {
        $this->clearsCount++;
    }

    public function getClearsCount(): int
    {
        return $this->clearsCount;
    }
}
