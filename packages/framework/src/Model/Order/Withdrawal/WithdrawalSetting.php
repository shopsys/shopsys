<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

use Shopsys\FrameworkBundle\Component\Setting\Setting;

class WithdrawalSetting
{
    protected const string WITHDRAWAL_DEADLINE_DAYS = 'withdrawalDeadlineDays';
    protected const string WITHDRAWAL_INSTRUCTIONS = 'withdrawalInstructions';

    public function __construct(
        protected readonly Setting $setting,
    ) {
    }

    public function getInstructions(int $domainId): string
    {
        return $this->setting->getForDomain(
            static::WITHDRAWAL_INSTRUCTIONS,
            $domainId,
        );
    }

    public function getDeadlineDays(int $domainId): int
    {
        return $this->setting->getForDomain(
            static::WITHDRAWAL_DEADLINE_DAYS,
            $domainId,
        );
    }

    public function setInstructions(string $instructions, int $domainId): void
    {
        $this->setting->setForDomain(
            static::WITHDRAWAL_INSTRUCTIONS,
            $instructions,
            $domainId,
        );
    }

    public function setDeadlineDays(int $deadlineDays, int $domainId): void
    {
        $this->setting->setForDomain(
            static::WITHDRAWAL_DEADLINE_DAYS,
            $deadlineDays,
            $domainId,
        );
    }
}
