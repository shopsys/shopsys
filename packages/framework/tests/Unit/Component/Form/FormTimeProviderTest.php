<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Form;

use DateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Form\FormTimeProvider;
use Shopsys\FrameworkBundle\Component\Form\TimedFormTypeExtension;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

class FormTimeProviderTest extends TestCase
{
    public static function isFormTimeValidProvider()
    {
        return [
            [9, '-10 second', true],
            [11, '-10 second', false],
        ];
    }

    /**
     * @param int $minimumSeconds
     * @param string $formCreatedAt
     * @param bool $isValid
     */
    #[DataProvider('isFormTimeValidProvider')]
    public function testIsFormTimeValid($minimumSeconds, $formCreatedAt, $isValid)
    {
        $sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get', 'has'])
            ->getMock();
        $requestStackMock = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSession'])
            ->getMock();
        $sessionMock->expects($this->atLeastOnce())->method('get')->willReturn(new DateTime($formCreatedAt));
        $sessionMock->expects($this->atLeastOnce())->method('has')->willReturn(true);
        $requestStackMock->expects($this->atLeastOnce())->method('getSession')->willReturn($sessionMock);

        $formTimeProvider = new FormTimeProvider($requestStackMock);

        $options[TimedFormTypeExtension::OPTION_MINIMUM_SECONDS] = $minimumSeconds;
        $this->assertSame($isValid, $formTimeProvider->isFormTimeValid('formName', $options));
    }
}
