<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\GiftVoucher;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\GiftVoucher\Exception\UniqueGiftVoucherCodeGenerationFailedException;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherCodeGenerator;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeRepository;

class GiftVoucherCodeGeneratorTest extends TestCase
{
    public function testGeneratedCodeHasExpectedLengthAndAllowedCharactersOnly(): void
    {
        $giftVoucherCodeGenerator = $this->createGiftVoucherCodeGenerator(
            giftVoucherCodeExists: false,
            promoCodeExists: false,
        );

        $code = $giftVoucherCodeGenerator->generateUniqueCode();

        $this->assertSame(GiftVoucherCodeGenerator::CODE_LENGTH, strlen($code));
        $this->assertMatchesRegularExpression(
            '/^[' . GiftVoucherCodeGenerator::CODE_ALPHABET . ']+$/',
            $code,
        );
    }

    public function testGeneratorRetriesUntilCodeIsUniqueAcrossGiftVouchers(): void
    {
        $giftVoucherRepositoryStub = $this->createStub(GiftVoucherRepository::class);
        $giftVoucherRepositoryStub->method('existsByCode')->willReturn(true, true, false);
        $promoCodeRepositoryStub = $this->createStub(PromoCodeRepository::class);
        $promoCodeRepositoryStub->method('existsByCode')->willReturn(false);
        $giftVoucherCodeGenerator = new GiftVoucherCodeGenerator(
            $giftVoucherRepositoryStub,
            $promoCodeRepositoryStub,
        );

        $code = $giftVoucherCodeGenerator->generateUniqueCode();

        $this->assertSame(GiftVoucherCodeGenerator::CODE_LENGTH, strlen($code));
    }

    public function testGeneratorRejectsCodesCollidingWithExistingPromoCodes(): void
    {
        $giftVoucherRepositoryStub = $this->createStub(GiftVoucherRepository::class);
        $giftVoucherRepositoryStub->method('existsByCode')->willReturn(false);
        $promoCodeRepositoryStub = $this->createStub(PromoCodeRepository::class);
        $promoCodeRepositoryStub->method('existsByCode')->willReturn(true, false);
        $giftVoucherCodeGenerator = new GiftVoucherCodeGenerator(
            $giftVoucherRepositoryStub,
            $promoCodeRepositoryStub,
        );

        $code = $giftVoucherCodeGenerator->generateUniqueCode();

        $this->assertSame(GiftVoucherCodeGenerator::CODE_LENGTH, strlen($code));
    }

    public function testGenerationFailsWhenNoUniqueCodeCanBeFound(): void
    {
        $giftVoucherCodeGenerator = $this->createGiftVoucherCodeGenerator(
            giftVoucherCodeExists: true,
            promoCodeExists: false,
        );

        $this->expectException(UniqueGiftVoucherCodeGenerationFailedException::class);

        $giftVoucherCodeGenerator->generateUniqueCode();
    }

    /**
     * @return iterable<string, array{enteredCode: string, expectedNormalizedCode: string}>
     */
    public static function getCodeNormalizationData(): iterable
    {
        yield 'lowercase input is uppercased' => [
            'enteredCode' => 'acdefhjkmnpr',
            'expectedNormalizedCode' => 'ACDEFHJKMNPR',
        ];

        yield 'spaces are stripped' => [
            'enteredCode' => 'ACDE FHJK MNPR',
            'expectedNormalizedCode' => 'ACDEFHJKMNPR',
        ];

        yield 'dashes are stripped' => [
            'enteredCode' => 'ACDE-FHJK-MNPR',
            'expectedNormalizedCode' => 'ACDEFHJKMNPR',
        ];

        yield 'mixed separators and case are normalized' => [
            'enteredCode' => ' acde-FHJK mnpr ',
            'expectedNormalizedCode' => 'ACDEFHJKMNPR',
        ];
    }

    #[DataProvider('getCodeNormalizationData')]
    public function testEnteredCodeIsNormalizedToUppercaseWithoutSeparators(
        string $enteredCode,
        string $expectedNormalizedCode,
    ): void {
        $giftVoucherCodeGenerator = $this->createGiftVoucherCodeGenerator(
            giftVoucherCodeExists: false,
            promoCodeExists: false,
        );

        $normalizedCode = $giftVoucherCodeGenerator->normalizeCode($enteredCode);

        $this->assertSame($expectedNormalizedCode, $normalizedCode);
    }

    private function createGiftVoucherCodeGenerator(
        bool $giftVoucherCodeExists,
        bool $promoCodeExists,
    ): GiftVoucherCodeGenerator {
        $giftVoucherRepositoryStub = $this->createStub(GiftVoucherRepository::class);
        $giftVoucherRepositoryStub->method('existsByCode')->willReturn($giftVoucherCodeExists);
        $promoCodeRepositoryStub = $this->createStub(PromoCodeRepository::class);
        $promoCodeRepositoryStub->method('existsByCode')->willReturn($promoCodeExists);

        return new GiftVoucherCodeGenerator($giftVoucherRepositoryStub, $promoCodeRepositoryStub);
    }
}
