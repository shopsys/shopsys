<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\FlashMessage;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class FlashMessageServiceTest extends TestCase
{
    private const string XSS_PAYLOAD = '<img src=x onerror="alert(1)">';
    private const string ESCAPED_XSS_PAYLOAD = '&lt;img src=x onerror=&quot;alert(1)&quot;&gt;';

    private FlashMessageService $flashMessageService;

    #[Override]
    protected function setUp(): void
    {
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $this->flashMessageService = new FlashMessageService($requestStack, new Environment(new ArrayLoader()));
    }

    /**
     * @return iterable<string, array{addMethod: string, getMethod: string}>
     */
    public static function getPlainFlashMethodsData(): iterable
    {
        yield 'success' => [
            'addMethod' => 'addSuccessFlash',
            'getMethod' => 'getSuccessMessages',
        ];

        yield 'error' => [
            'addMethod' => 'addErrorFlash',
            'getMethod' => 'getErrorMessages',
        ];

        yield 'info' => [
            'addMethod' => 'addInfoFlash',
            'getMethod' => 'getInfoMessages',
        ];

        yield 'warning' => [
            'addMethod' => 'addWarningFlash',
            'getMethod' => 'getWarningMessages',
        ];
    }

    #[DataProvider('getPlainFlashMethodsData')]
    public function testPlainFlashMessageIsEscaped(string $addMethod, string $getMethod): void
    {
        $this->flashMessageService->{$addMethod}('Name "' . self::XSS_PAYLOAD . '" is invalid');

        $this->assertSame(
            ['Name &quot;' . self::ESCAPED_XSS_PAYLOAD . '&quot; is invalid'],
            $this->flashMessageService->{$getMethod}(),
        );
    }

    /**
     * @return iterable<string, array{addMethod: string, getMethod: string}>
     */
    public static function getTwigFlashMethodsData(): iterable
    {
        yield 'success' => ['addMethod' => 'addSuccessFlashTwig', 'getMethod' => 'getSuccessMessages'];

        yield 'error' => ['addMethod' => 'addErrorFlashTwig', 'getMethod' => 'getErrorMessages'];

        yield 'info' => ['addMethod' => 'addInfoFlashTwig', 'getMethod' => 'getInfoMessages'];

        yield 'warning' => ['addMethod' => 'addWarningFlashTwig', 'getMethod' => 'getWarningMessages'];
    }

    #[DataProvider('getTwigFlashMethodsData')]
    public function testTwigFlashMessageKeepsTemplateMarkupAndEscapesParameters(
        string $addMethod,
        string $getMethod,
    ): void {
        $this->flashMessageService->{$addMethod}('Item <strong>{{ name }}</strong> deleted', ['name' => self::XSS_PAYLOAD]);

        $this->assertSame(
            ['Item <strong>' . self::ESCAPED_XSS_PAYLOAD . '</strong> deleted'],
            $this->flashMessageService->{$getMethod}(),
        );
    }
}
