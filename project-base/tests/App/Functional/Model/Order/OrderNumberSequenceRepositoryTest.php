<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class OrderNumberSequenceRepositoryTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository
     * @inject
     */
    private OrderNumberSequenceRepository $orderNumberSequenceRepository;

    public function testGetNextNumber()
    {
        $numbers = [];
        for ($i = 0; $i < 10; $i++) {
            $numbers[] = $this->orderNumberSequenceRepository->getNextNumber();
        }

        $uniqueNumbers = array_unique($numbers);

        $this->assertSame(count($numbers), count($uniqueNumbers));
    }
}
