<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\PromoCode;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\InvalidPromoCodeException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CurrentPromoCodeFacadeTest extends TestCase
{
    public function testGetEnteredPromoCode()
    {
        $validPromoCodeData = new PromoCodeData();
        $validPromoCodeData->code = 'validCode';
        $validPromoCodeData->percent = 10.0;
        $validPromoCode = new PromoCode($validPromoCodeData);

        $sessionMock = $this->getMockForAbstractClass(SessionInterface::class);
        $sessionMock->expects($this->atLeastOnce())->method('get')->willReturn($validPromoCode->getCode());

        $requestStackMock = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSession'])
            ->getMock();
        $requestStackMock->expects($this->atLeastOnce())->method('getSession')->willReturn($sessionMock);

        $emMock = $this->createMock(EntityManager::class);
        $promoCodeRepositoryMock = $this->getMockBuilder(PromoCodeRepository::class)
            ->setMethods(['findByCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $promoCodeRepositoryMock->expects($this->atLeastOnce())->method('findByCode')->willReturn($validPromoCode);

        $promoCodeFacade = new PromoCodeFacade(
            $emMock,
            $promoCodeRepositoryMock,
            new PromoCodeFactory(new EntityNameResolver([])),
        );
        $currentPromoCodeFacade = new CurrentPromoCodeFacade($promoCodeFacade, $requestStackMock);

        $this->assertSame($validPromoCode, $currentPromoCodeFacade->getValidEnteredPromoCodeOrNull());
    }

    public function testGetEnteredPromoCodeInvalid()
    {
        $validPromoCodeData = new PromoCodeData();
        $validPromoCodeData->code = 'validCode';
        $validPromoCodeData->percent = 10.0;
        $validPromoCode = new PromoCode($validPromoCodeData);

        $sessionMock = $this->getMockForAbstractClass(SessionInterface::class);
        $sessionMock->expects($this->atLeastOnce())->method('get')->willReturn($validPromoCode->getCode());

        $requestStackMock = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSession'])
            ->getMock();
        $requestStackMock->expects($this->atLeastOnce())->method('getSession')->willReturn($sessionMock);

        $emMock = $this->createMock(EntityManager::class);
        $promoCodeRepositoryMock = $this->getMockBuilder(PromoCodeRepository::class)
            ->setMethods(['findByCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $promoCodeRepositoryMock->expects($this->atLeastOnce())->method('findByCode')->willReturn(null);

        $promoCodeFacade = new PromoCodeFacade(
            $emMock,
            $promoCodeRepositoryMock,
            new PromoCodeFactory(new EntityNameResolver([])),
        );
        $currentPromoCodeFacade = new CurrentPromoCodeFacade($promoCodeFacade, $requestStackMock);

        $this->assertNull($currentPromoCodeFacade->getValidEnteredPromoCodeOrNull());
    }

    public function testSetEnteredPromoCode()
    {
        $enteredCode = 'validCode';
        $validPromoCodeData = new PromoCodeData();
        $validPromoCodeData->code = 'validCode';
        $validPromoCodeData->percent = 10.0;
        $validPromoCode = new PromoCode($validPromoCodeData);

        $sessionMock = $this->getMockForAbstractClass(SessionInterface::class);
        $sessionMock->expects($this->atLeastOnce())->method('set')->with(
            $this->anything(),
            $this->equalTo($enteredCode),
        );

        $requestStackMock = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSession'])
            ->getMock();
        $requestStackMock->expects($this->atLeastOnce())->method('getSession')->willReturn($sessionMock);

        $emMock = $this->createMock(EntityManager::class);
        $promoCodeRepositoryMock = $this->getMockBuilder(PromoCodeRepository::class)
            ->setMethods(['findByCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $promoCodeRepositoryMock->expects($this->atLeastOnce())->method('findByCode')->willReturn($validPromoCode);

        $promoCodeFacade = new PromoCodeFacade(
            $emMock,
            $promoCodeRepositoryMock,
            new PromoCodeFactory(new EntityNameResolver([])),
        );
        $currentPromoCodeFacade = new CurrentPromoCodeFacade($promoCodeFacade, $requestStackMock);
        $currentPromoCodeFacade->setEnteredPromoCode($enteredCode);
    }

    public function testSetEnteredPromoCodeInvalid()
    {
        $enteredCode = 'invalidCode';

        $sessionMock = $this->getMockForAbstractClass(SessionInterface::class);
        $sessionMock->expects($this->never())->method('set');

        $requestStackMock = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSession'])
            ->getMock();
        $requestStackMock->method('getSession')->willReturn($sessionMock);

        $emMock = $this->createMock(EntityManager::class);
        $promoCodeRepositoryMock = $this->getMockBuilder(PromoCodeRepository::class)
            ->setMethods(['findByCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $promoCodeRepositoryMock->expects($this->atLeastOnce())->method('findByCode')->willReturn(null);

        $promoCodeFacade = new PromoCodeFacade(
            $emMock,
            $promoCodeRepositoryMock,
            new PromoCodeFactory(new EntityNameResolver([])),
        );
        $currentPromoCodeFacade = new CurrentPromoCodeFacade($promoCodeFacade, $requestStackMock);
        $this->expectException(InvalidPromoCodeException::class);
        $currentPromoCodeFacade->setEnteredPromoCode($enteredCode);
    }
}
