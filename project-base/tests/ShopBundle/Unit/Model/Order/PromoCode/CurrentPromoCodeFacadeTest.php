<?php

namespace Tests\ShopBundle\Unit\Model\Order\PromoCode;

use Doctrine\ORM\EntityManager;
use PHPUnit_Framework_TestCase;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CurrentPromoCodeFacadeTest extends PHPUnit_Framework_TestCase
{
    public function testGetEnteredPromoCode()
    {
        $validPromoCode = new PromoCode(new PromoCodeData('validCode', 10.0));
        $sessionMock = $this->getMockForAbstractClass(SessionInterface::class, ['get']);
        $sessionMock->expects($this->atLeastOnce())->method('get')->willReturn($validPromoCode->getCode());
        $emMock = $this->createMock(EntityManager::class);
        $promoCodeRepositoryMock = $this->getMockBuilder(PromoCodeRepository::class)
            ->setMethods(['findByCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $promoCodeRepositoryMock->expects($this->atLeastOnce())->method('findByCode')->willReturn($validPromoCode);

        $promoCodeFacade = new PromoCodeFacade($emMock, $promoCodeRepositoryMock);
        $currentPromoCodeFacade = new CurrentPromoCodeFacade($promoCodeFacade, $sessionMock);

        $this->assertSame($validPromoCode, $currentPromoCodeFacade->getValidEnteredPromoCodeOrNull());
    }

    public function testGetEnteredPromoCodeInvalid()
    {
        $validPromoCode = new PromoCode(new PromoCodeData('validCode', 10.0));
        $sessionMock = $this->getMockForAbstractClass(SessionInterface::class, ['get']);
        $sessionMock->expects($this->atLeastOnce())->method('get')->willReturn($validPromoCode->getCode());
        $emMock = $this->createMock(EntityManager::class);
        $promoCodeRepositoryMock = $this->getMockBuilder(PromoCodeRepository::class)
            ->setMethods(['findByCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $promoCodeRepositoryMock->expects($this->atLeastOnce())->method('findByCode')->willReturn(null);

        $promoCodeFacade = new PromoCodeFacade($emMock, $promoCodeRepositoryMock);
        $currentPromoCodeFacade = new CurrentPromoCodeFacade($promoCodeFacade, $sessionMock);

        $this->assertNull($currentPromoCodeFacade->getValidEnteredPromoCodeOrNull());
    }

    public function testSetEnteredPromoCode()
    {
        $enteredCode = 'validCode';
        $validPromoCode = new PromoCode(new PromoCodeData('validCode', 10.0));
        $sessionMock = $this->getMockForAbstractClass(SessionInterface::class, ['get']);
        $sessionMock->expects($this->atLeastOnce())->method('set')->with(
            $this->anything(),
            $this->equalTo($enteredCode)
        );

        $emMock = $this->createMock(EntityManager::class);
        $promoCodeRepositoryMock = $this->getMockBuilder(PromoCodeRepository::class)
            ->setMethods(['findByCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $promoCodeRepositoryMock->expects($this->atLeastOnce())->method('findByCode')->willReturn($validPromoCode);

        $promoCodeFacade = new PromoCodeFacade($emMock, $promoCodeRepositoryMock);
        $currentPromoCodeFacade = new CurrentPromoCodeFacade($promoCodeFacade, $sessionMock);
        $currentPromoCodeFacade->setEnteredPromoCode($enteredCode);
    }

    public function testSetEnteredPromoCodeInvalid()
    {
        $enteredCode = 'invalidCode';
        $sessionMock = $this->getMockForAbstractClass(SessionInterface::class, ['get']);
        $sessionMock->expects($this->never())->method('set');

        $emMock = $this->createMock(EntityManager::class);
        $promoCodeRepositoryMock = $this->getMockBuilder(PromoCodeRepository::class)
            ->setMethods(['findByCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $promoCodeRepositoryMock->expects($this->atLeastOnce())->method('findByCode')->willReturn(null);

        $promoCodeFacade = new PromoCodeFacade($emMock, $promoCodeRepositoryMock);
        $currentPromoCodeFacade = new CurrentPromoCodeFacade($promoCodeFacade, $sessionMock);
        $this->expectException(\Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\InvalidPromoCodeException::class);
        $currentPromoCodeFacade->setEnteredPromoCode($enteredCode);
    }
}
