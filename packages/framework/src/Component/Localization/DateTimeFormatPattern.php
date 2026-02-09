<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Localization;

class DateTimeFormatPattern
{
    /**
     * @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     */
    protected ?int $dateType = null;

    /**
     * @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     */
    protected ?int $timeType = null;

    /**
     * @param int|null $dateType @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     * @param int|null $timeType @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     */
    public function __construct(
        protected string $pattern,
        protected string $locale,
        ?int $dateType = null,
        ?int $timeType = null,
    ) {
        $this->dateType = $dateType;
        $this->timeType = $timeType;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @return int|null @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     */
    public function getDateType(): ?int
    {
        return $this->dateType;
    }

    /**
     * @return int|null @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     */
    public function getTimeType(): ?int
    {
        return $this->timeType;
    }
}
