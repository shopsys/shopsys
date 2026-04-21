<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form\Admin\PhonePrefix;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Form\Admin\PhonePrefix\CountryDialCodeTransformer;
use Shopsys\FrameworkBundle\Model\PhonePrefix\CountryDialCode;

final class CountryDialCodeTransformerTest extends TestCase
{
    private const string CODE_CZ = 'CZ';
    private const string CODE_SK = 'SK';
    private const string CODE_DE = 'DE';

    private CountryDialCode $cz;

    private CountryDialCode $sk;

    private CountryDialCode $de;

    #[Override]
    protected function setUp(): void
    {
        $this->cz = new CountryDialCode(self::CODE_CZ, '+420');
        $this->sk = new CountryDialCode(self::CODE_SK, '+421');
        $this->de = new CountryDialCode(self::CODE_DE, '+49');
    }

    // --- single mode: transform() ---

    public function testTransformNullReturnsNull(): void
    {
        $transformer = new CountryDialCodeTransformer([$this->cz, $this->sk]);

        $result = $transformer->transform(null);

        $this->assertNull($result);
    }

    public function testTransformKnownCodeReturnsMatchingCountryDialCode(): void
    {
        $transformer = new CountryDialCodeTransformer([$this->cz, $this->sk]);

        $result = $transformer->transform(self::CODE_CZ);

        $this->assertSame($this->cz, $result);
    }

    public function testTransformUnknownCodeReturnsNull(): void
    {
        $transformer = new CountryDialCodeTransformer([$this->cz, $this->sk]);

        $result = $transformer->transform('XX');

        $this->assertNull($result);
    }

    // --- single mode: reverseTransform() ---

    public function testReverseTransformCountryDialCodeReturnsCode(): void
    {
        $transformer = new CountryDialCodeTransformer([$this->cz, $this->sk]);

        $result = $transformer->reverseTransform($this->sk);

        $this->assertSame(self::CODE_SK, $result);
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function getNonCountryDialCodeValues(): iterable
    {
        yield 'null' => [null];

        yield 'string' => [self::CODE_CZ];

        yield 'integer' => [42];

        yield 'empty array' => [[]];
    }

    #[DataProvider('getNonCountryDialCodeValues')]
    public function testReverseTransformNonCountryDialCodeReturnsNull(mixed $value): void
    {
        $transformer = new CountryDialCodeTransformer([$this->cz, $this->sk]);

        $result = $transformer->reverseTransform($value);

        $this->assertNull($result);
    }

    // --- multiple mode: transform() ---

    public function testTransformMultipleKnownCodesReturnsMatchingObjects(): void
    {
        $transformer = new CountryDialCodeTransformer([$this->cz, $this->sk, $this->de], multiple: true);

        $result = $transformer->transform([self::CODE_CZ, self::CODE_DE]);

        $this->assertSame([$this->cz, $this->de], $result);
    }

    public function testTransformMultipleFiltersOutUnknownCodes(): void
    {
        $transformer = new CountryDialCodeTransformer([$this->cz, $this->sk], multiple: true);

        $result = $transformer->transform([self::CODE_CZ, 'XX', self::CODE_SK]);

        $this->assertSame([$this->cz, $this->sk], $result);
    }

    public function testTransformMultipleEmptyArrayReturnsEmptyArray(): void
    {
        $transformer = new CountryDialCodeTransformer([$this->cz, $this->sk], multiple: true);

        $result = $transformer->transform([]);

        $this->assertSame([], $result);
    }

    public function testTransformMultipleAllUnknownCodesReturnsEmptyArray(): void
    {
        $transformer = new CountryDialCodeTransformer([$this->cz, $this->sk], multiple: true);

        $result = $transformer->transform(['XX', 'YY']);

        $this->assertSame([], $result);
    }

    public function testTransformMultiplePreservesOrder(): void
    {
        $transformer = new CountryDialCodeTransformer([$this->cz, $this->sk, $this->de], multiple: true);

        $result = $transformer->transform([self::CODE_DE, self::CODE_CZ]);

        $this->assertSame([$this->de, $this->cz], $result);
    }

    // --- multiple mode: reverseTransform() ---

    public function testReverseTransformMultipleCountryDialCodesReturnsCodes(): void
    {
        $transformer = new CountryDialCodeTransformer([$this->cz, $this->sk, $this->de], multiple: true);

        $result = $transformer->reverseTransform([$this->cz, $this->de]);

        $this->assertSame([self::CODE_CZ, self::CODE_DE], $result);
    }

    public function testReverseTransformMultipleEmptyArrayReturnsEmptyArray(): void
    {
        $transformer = new CountryDialCodeTransformer([$this->cz, $this->sk], multiple: true);

        $result = $transformer->reverseTransform([]);

        $this->assertSame([], $result);
    }

    public function testReverseTransformMultipleSingleItemArray(): void
    {
        $transformer = new CountryDialCodeTransformer([$this->cz, $this->sk], multiple: true);

        $result = $transformer->reverseTransform([$this->sk]);

        $this->assertSame([self::CODE_SK], $result);
    }
}
