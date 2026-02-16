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
        $regularFlag = $this->createFlagStub(1, false);
        $promotionFlag = $this->createFlagStub(2, true);

        $productMock = $this->createProductMockWithFlags([$regularFlag, $promotionFlag]);

        $productMock->expects($this->any())->method('getPromotionXy')
            ->willReturn(null);

        $productMock->expects($this->any())->method('isCalculatedSellingDenied')
            ->willReturn(false);

        $productMock->expects($this->once())
            ->method('setFlags')
            ->with([self::DOMAIN_ID => [$regularFlag]]);

        $promotionFlagFacade = $this->createPromotionFlagFacade($productMock);

        $promotionFlagFacade->updatePromotionFlags(1);
    }

    public function testRemovePromotionFlagsWhenProductIsNotSellable(): void
    {
        $regularFlag = $this->createFlagStub(1, false);
        $promotionFlag = $this->createFlagStub(2, true);
        $promotionXyStub = $this->createPromotionXyStub(3, 1);

        $productMock = $this->createProductMockWithFlags([$regularFlag, $promotionFlag]);

        $productMock->expects($this->any())->method('getPromotionXy')
            ->willReturn($promotionXyStub);

        $productMock->expects($this->any())->method('isCalculatedSellingDenied')
            ->willReturn(true);

        $productMock->expects($this->once())
            ->method('setFlags')
            ->with([self::DOMAIN_ID => [$regularFlag]]);

        $promotionFlagFacade = $this->createPromotionFlagFacade($productMock);

        $promotionFlagFacade->updatePromotionFlags(1);
    }

    public function testAddCorrectPromotionFlagWhenProductHasPromotionAndIsSellable(): void
    {
        $regularFlag = $this->createFlagStub(1, false);
        $correctPromotionFlag = $this->createFlagStub(2, true);
        $promotionXyStub = $this->createPromotionXyStub(3, 1);

        $productMock = $this->createProductMockWithFlags([$regularFlag]);

        $productMock->expects($this->any())->method('getPromotionXy')
            ->willReturn($promotionXyStub);

        $productMock->expects($this->any())->method('isCalculatedSellingDenied')
            ->willReturn(false);

        $productMock->expects($this->once())
            ->method('setFlags')
            ->with([self::DOMAIN_ID => [$regularFlag, $correctPromotionFlag]]);

        $promotionFlagFacade = $this->createPromotionFlagFacade($productMock, $correctPromotionFlag);

        $promotionFlagFacade->updatePromotionFlags(1);
    }

    public function testReplaceWrongPromotionFlagWithCorrectOne(): void
    {
        $regularFlag = $this->createFlagStub(1, false);
        $wrongPromotionFlag = $this->createFlagStub(2, true);
        $correctPromotionFlag = $this->createFlagStub(3, true);
        $promotionXyStub = $this->createPromotionXyStub(5, 2);

        $productMock = $this->createProductMockWithFlags([$regularFlag, $wrongPromotionFlag]);

        $productMock->expects($this->any())->method('getPromotionXy')
            ->willReturn($promotionXyStub);

        $productMock->expects($this->any())->method('isCalculatedSellingDenied')
            ->willReturn(false);

        $productMock->expects($this->once())
            ->method('setFlags')
            ->with([self::DOMAIN_ID => [$regularFlag, $correctPromotionFlag]]);

        $promotionFlagFacade = $this->createPromotionFlagFacade($productMock, $correctPromotionFlag);

        $promotionFlagFacade->updatePromotionFlags(1);
    }

    public function testNoFlushWhenNoChangesOnProductWithoutPromotion(): void
    {
        $regularFlag = $this->createFlagStub(1, false);

        $productMock = $this->createProductMockWithFlags([$regularFlag]);

        $productMock->expects($this->any())->method('getPromotionXy')
            ->willReturn(null);

        $productMock->expects($this->any())->method('isCalculatedSellingDenied')
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
        ?Flag $promotionFlagToReturn = null,
        EntityManagerInterface|MockObject|null $emMock = null,
    ): PromotionFlagFacade {
        $flagFacadeStub = $this->createStub(FlagFacade::class);
        $flagDataFactoryStub = $this->createStub(FlagDataFactory::class);

        $domainStub = $this->createStub(Domain::class);
        $domainStub->method('getAllIds')
            ->willReturn([self::DOMAIN_ID]);

        $productPromotionXyRepositoryStub = $this->createStub(ProductPromotionXyRepository::class);

        if ($promotionFlagToReturn !== null) {
            $productPromotionXyRepositoryStub->method('findFlagByQuantities')
                ->willReturn($promotionFlagToReturn);
        }

        $productPromotionXyFactoryStub = $this->createStub(ProductPromotionXyFactory::class);
        $productPromotionXyDataFactoryStub = $this->createStub(ProductPromotionXyDataFactory::class);

        $productFacadeMock = $this->createMock(ProductFacade::class);
        $productFacadeMock->expects($this->any())->method('getById')
            ->with(1)->willReturn($productMock);

        return new PromotionFlagFacade(
            $flagFacadeStub,
            $emMock ?? $this->createStub(EntityManagerInterface::class),
            $flagDataFactoryStub,
            $domainStub,
            $productPromotionXyRepositoryStub,
            $productPromotionXyFactoryStub,
            $productPromotionXyDataFactoryStub,
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

        $productMock->expects($this->any())->method('getFlags')
            ->willReturn($initialFlags);

        return $productMock;
    }

    private function createFlagStub(int $id, bool $hasPromotionXy): Flag
    {
        $flagStub = $this->createStub(Flag::class);

        $flagStub->method('getId')->willReturn($id);
        $flagStub->method('hasPromotionXy')->willReturn($hasPromotionXy);

        return $flagStub;
    }

    private function createPromotionXyStub(?int $buyQuantity, ?int $freeQuantity): ProductPromotionXy
    {
        $promotionXyStub = $this->createStub(ProductPromotionXy::class);

        $promotionXyStub->method('getBuyQuantity')->willReturn($buyQuantity);
        $promotionXyStub->method('getFreeQuantity')->willReturn($freeQuantity);

        return $promotionXyStub;
    }
}
