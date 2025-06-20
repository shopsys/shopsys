<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Twig\Runtime;

use DateTimeImmutable;
use DateTimeInterface;
use IntlDateFormatter;
use LogicException;
use Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatterInterface;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorLocalizationFacade;
use Twig\Extension\RuntimeExtensionInterface;

class DateTimeAgoRuntime implements RuntimeExtensionInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatterInterface $dateTimeFormatter
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorLocalizationFacade $administratorLocalizationFacade
     */
    public function __construct(
        protected readonly DateTimeFormatterInterface $dateTimeFormatter,
        protected readonly AdministratorLocalizationFacade $administratorLocalizationFacade,
    ) {
    }

    /**
     * @param \DateTimeInterface|null $dateTime
     * @return string
     */
    public function dateTimeAgo(?DateTimeInterface $dateTime): string
    {
        if ($dateTime === null) {
            return '';
        }

        $today = (new DateTimeImmutable())->format('Y-m-d');
        $yesterday = (new DateTimeImmutable('yesterday'))->format('Y-m-d');
        $dateStr = $dateTime->format('Y-m-d');

        if ($dateStr !== $today && $dateStr !== $yesterday) {
            return $this->dateTimeFormatter->format(
                $dateTime,
                IntlDateFormatter::MEDIUM,
                IntlDateFormatter::MEDIUM,
                $this->administratorLocalizationFacade->getCurrentAdminLocaleOrDefault(),
            );
        }

        $datePart = match ($dateStr) {
            $today => 'today',
            $yesterday => 'yesterday',
            default => throw new LogicException('Date should be either today or yesterday at this point'),
        };

        return $datePart . ' ' . t('at') . ' ' . $this->dateTimeFormatter->format(
            $dateTime,
            IntlDateFormatter::NONE,
            IntlDateFormatter::MEDIUM,
            $this->administratorLocalizationFacade->getCurrentAdminLocaleOrDefault(),
        );
    }
}
