<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class ComplaintResolutionEnum extends AbstractEnum
{
    public const string FIX = 'fix';
    public const string EXCHANGE = 'exchange';
    public const string MONEY_RETURN = 'money_return';

    /**
     * @return array<string, string>
     */
    public function getAllIndexedByTranslations(): array
    {
        return [
            t('Fix') => static::FIX,
            t('Exchange') => static::EXCHANGE,
            t('Money return') => static::MONEY_RETURN,
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function serialize(): array
    {
        $returnArray = [];

        foreach ($this->getAllIndexedByTranslations() as $name => $value) {
            $returnArray[$value] = [
                'name' => $name,
                'value' => $value,
            ];
        }

        return $returnArray;
    }

    /**
     * @param string $value
     * @return bool
     */
    public function isMoneyReturn(string $value): bool
    {
        return $value === static::MONEY_RETURN;
    }
}
