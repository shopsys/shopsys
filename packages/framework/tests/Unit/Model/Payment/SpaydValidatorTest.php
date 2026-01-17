<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Payment;

use InvalidArgumentException;
use Shopsys\FrameworkBundle\Model\Payment\SpaydValidator;
use Tests\FrameworkBundle\Unit\TestCase;

class SpaydValidatorTest extends TestCase
{
    public function testValidMinimalSpayd(): void
    {
        $validator = new SpaydValidator();
        $spayd = 'SPD*1.0*ACC:SK3112000000198742637541*AM:123.45*CC:EUR';

        $this->assertTrue($this->isValid($validator, $spayd));
    }

    public function testValidSpaydWithBicAndVs(): void
    {
        $validator = new SpaydValidator();
        $spayd = 'SPD*1.0*ACC:CZ6508000000192000145399+GIBACZPX*AM:1000*CC:CZK*X-VS:20240001';

        $this->assertTrue($this->isValid($validator, $spayd));
    }

    public function testValidNonCzSkIban(): void
    {
        $validator = new SpaydValidator();
        $spayd = 'SPD*1.0*ACC:DE89370400440532013000*AM:50*CC:EUR';

        $this->assertTrue($this->isValid($validator, $spayd));
    }

    private function isValid(SpaydValidator $validator, string $spayd): bool
    {
        try {
            $validator->validate($spayd);

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    public function testInvalidHeader(): void
    {
        $validator = new SpaydValidator();
        $spayd = 'SPAYD*1.0*ACC:SK3112000000198742637541*AM:1*CC:EUR';


        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must start with "SPD"');
        $validator->validate($spayd);
    }

    public function testUnsupportedVersion(): void
    {
        $validator = new SpaydValidator();
        $spayd = 'SPD*9.9*ACC:SK3112000000198742637541*AM:1*CC:EUR';


        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported SPAYD version');
        $validator->validate($spayd);
    }

    public function testMissingRequiredFields(): void
    {
        $validator = new SpaydValidator();
        // chybí CC
        $spayd = 'SPD*1.0*ACC:SK3112000000198742637541*AM:10';


        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required SPAYD field');
        $validator->validate($spayd);
    }

    public function testInvalidIbanChecksum(): void
    {
        $validator = new SpaydValidator();
        // Poslední číslice změněna z 9 na 8 (rozbije checksum)
        $spayd = 'SPD*1.0*ACC:CZ6508000000192000145398*AM:10*CC:CZK';


        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid IBAN checksum');
        $validator->validate($spayd);
    }

    public function testInvalidAmountFormat(): void
    {
        $validator = new SpaydValidator();
        $spayd = 'SPD*1.0*ACC:SK3112000000198742637541*AM:12.345*CC:EUR';


        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid AM (amount) format');
        $validator->validate($spayd);
    }

    public function testInvalidAmountNonPositive(): void
    {
        $validator = new SpaydValidator();
        $spaydZero = 'SPD*1.0*ACC:SK3112000000198742637541*AM:0*CC:EUR';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('AM (amount) must be a positive number.');
        $validator->validate($spaydZero);
    }

    public function testInvalidAmountNegative(): void
    {
        $validator = new SpaydValidator();
        $spaydNegative = 'SPD*1.0*ACC:SK3112000000198742637541*AM:-10*CC:EUR';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid AM (amount) format. Expect digits with optional dot and up to 2 decimals.');
        $validator->validate($spaydNegative);
    }

    public function testInvalidCurrency(): void
    {
        $validator = new SpaydValidator();
        $spayd = 'SPD*1.0*ACC:SK3112000000198742637541*AM:10*CC:czk';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CC (currency) must be a 3-letter uppercase ISO code');
        $validator->validate($spayd);
    }

    public function testInvalidVariableSymbolNonNumeric(): void
    {
        $validator = new SpaydValidator();
        $spayd = 'SPD*1.0*ACC:CZ6508000000192000145399*AM:10*CC:CZK*X-VS:ABC';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('X-VS (variable symbol) must be 1 to 10 digits');
        $validator->validate($spayd);
    }

    public function testInvalidVariableSymbolTooLong(): void
    {
        $validator = new SpaydValidator();
        $spayd = 'SPD*1.0*ACC:CZ6508000000192000145399*AM:10*CC:CZK*X-VS:12345678901';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('X-VS (variable symbol) must be 1 to 10 digits');
        $validator->validate($spayd);
    }
}
