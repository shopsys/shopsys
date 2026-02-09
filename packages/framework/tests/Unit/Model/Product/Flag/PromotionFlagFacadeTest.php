<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product\Flag;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\PromotionFlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductPromotionXy;
use Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyDataFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyRepository;

class PromotionFlagFacadeTest extends TestCase
{
    private const int DOMAIN_ID = 1;

    public function testRemovePromotionFlagsWhenProductHasNoPromotion(): void
    {
        $regularFlag = $this->createFlagMock(1, false);
        $promotionFlag = $this->createFlagMock(2, true);

        $productMock = $this->createProductMockWithFlags([$regularFlag, $promotionFlag]);

        $productMock->method('getPromotionXy')
            ->willReturn(null);

        $productMock->method('isCalculatedSellingDenied')
            ->willReturn(false);

        $productMock->expects($this->once())
            ->method('setFlags')
            ->with([self::DOMAIN_ID => [$regularFlag]]);

        $promotionFlagFacade = $this->createPromotionFlagFacade($productMock);

        $promotionFlagFacade->updatePromotionFlags(1);
    }

    public function testRemovePromotionFlagsWhenProductIsNotSellable(): void
    {
        $regularFlag = $this->createFlagMock(1, false);
        $promotionFlag = $this->createFlagMock(2, true);
        $promotionXyMock = $this->createPromotionXyMock(3, 1);

        $productMock = $this->createProductMockWithFlags([$regularFlag, $promotionFlag]);

        $productMock->method('getPromotionXy')
            ->willReturn($promotionXyMock);

        $productMock->method('isCalculatedSellingDenied')
            ->willReturn(true);

        $productMock->expects($this->once())
            ->method('setFlags')
            ->with([self::DOMAIN_ID => [$regularFlag]]);

        $promotionFlagFacade = $this->createPromotionFlagFacade($productMock);

        $promotionFlagFacade->updatePromotionFlags(1);
    }

    public function testAddCorrectPromotionFlagWhenProductHasPromotionAndIsSellable(): void
    {
        $regularFlag = $this->createFlagMock(1, false);
        $correctPromotionFlag = $this->createFlagMock(2, true);
        $promotionXyMock = $this->createPromotionXyMock(3, 1);

        $productMock = $this->createProductMockWithFlags([$regularFlag]);

        $productMock->method('getPromotionXy')
            ->willReturn($promotionXyMock);

        $productMock->method('isCalculatedSellingDenied')
            ->willReturn(false);

        $productMock->expects($this->once())
            ->method('setFlags')
            ->with([self::DOMAIN_ID => [$regularFlag, $correctPromotionFlag]]);

        $promotionFlagFacade = $this->createPromotionFlagFacade($productMock, $correctPromotionFlag);

        $promotionFlagFacade->updatePromotionFlags(1);
    }

    public function testReplaceWrongPromotionFlagWithCorrectOne(): void
    {
        $regularFlag = $this->createFlagMock(1, false);
        $wrongPromotionFlag = $this->createFlagMock(2, true);
        $correctPromotionFlag = $this->createFlagMock(3, true);
        $promotionXyMock = $this->createPromotionXyMock(5, 2);

        $productMock = $this->createProductMockWithFlags([$regularFlag, $wrongPromotionFlag]);

        $productMock->method('getPromotionXy')
            ->willReturn($promotionXyMock);

        $productMock->method('isCalculatedSellingDenied')
            ->willReturn(false);

        $productMock->expects($this->once())
            ->method('setFlags')
            ->with([self::DOMAIN_ID => [$regularFlag, $correctPromotionFlag]]);

        $promotionFlagFacade = $this->createPromotionFlagFacade($productMock, $correctPromotionFlag);

        $promotionFlagFacade->updatePromotionFlags(1);
    }

    public function testNoFlushWhenNoChangesOnProductWithoutPromotion(): void
    {
        $regularFlag = $this->createFlagMock(1, false);

        $productMock = $this->createProductMockWithFlags([$regularFlag]);

        $productMock->method('getPromotionXy')
            ->willReturn(null);

        $productMock->method('isCalculatedSellingDenied')
            ->willReturn(false);

        $productMock->expects($this->never())
            ->method('setFlags');

        $emMock = $this->createMock(EntityManagerInterface::class);
        $emMock->expects($this->never())
            ->method('flush');

        $promotionFlagFacade = $this->createPromotionFlagFacade($productMock, null, $emMock);

        $promotionFlagFacade->updatePromotionFlags(1);
    }

    private function createPromotionFlagFacade(
        Product|MockObject $productMock,
        Flag|MockObject|null $promotionFlagToReturn = null,
        EntityManagerInterface|MockObject|null $emMock = null,
    ): PromotionFlagFacade {
        $flagFacadeMock = $this->createMock(FlagFacade::class);
        $flagDataFactoryMock = $this->createMock(FlagDataFactory::class);

        $domainMock = $this->createMock(Domain::class);
        $domainMock->method('getAllIds')
            ->willReturn([self::DOMAIN_ID]);

        $productPromotionXyRepositoryMock = $this->createMock(ProductPromotionXyRepository::class);

        if ($promotionFlagToReturn !== null) {
            $productPromotionXyRepositoryMock->method('findFlagByQuantities')
                ->willReturn($promotionFlagToReturn);
        }

        $productPromotionXyFactoryMock = $this->createMock(ProductPromotionXyFactory::class);
        $productPromotionXyDataFactoryMock = $this->createMock(ProductPromotionXyDataFactory::class);

        $productFacadeMock = $this->createMock(ProductFacade::class);
        $productFacadeMock->method('getById')
            ->with(1)
            ->willReturn($productMock);

        return new PromotionFlagFacade(
            $flagFacadeMock,
            $emMock ?? $this->createMock(EntityManagerInterface::class),
            $flagDataFactoryMock,
            $domainMock,
            $productPromotionXyRepositoryMock,
            $productPromotionXyFactoryMock,
            $productPromotionXyDataFactoryMock,
            $productFacadeMock,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[] $initialFlags
     */
    private function createProductMockWithFlags(array $initialFlags): Product|MockObject
    {
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFlags', 'getPromotionXy', 'isCalculatedSellingDenied', 'setFlags'])
            ->getMock();

        $productMock->method('getFlags')
            ->willReturn($initialFlags);

        return $productMock;
    }

    private function createFlagMock(int $id, bool $hasPromotionXy): Flag|MockObject
    {
        $flagMock = $this->getMockBuilder(Flag::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'hasPromotionXy'])
            ->getMock();

        $flagMock->method('getId')->willReturn($id);
        $flagMock->method('hasPromotionXy')->willReturn($hasPromotionXy);

        return $flagMock;
    }

    private function createPromotionXyMock(?int $buyQuantity, ?int $freeQuantity): ProductPromotionXy|MockObject
    {
        $promotionXyMock = $this->getMockBuilder(ProductPromotionXy::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBuyQuantity', 'getFreeQuantity'])
            ->getMock();

        $promotionXyMock->method('getBuyQuantity')->willReturn($buyQuantity);
        $promotionXyMock->method('getFreeQuantity')->willReturn($freeQuantity);

        return $promotionXyMock;
    }
}
