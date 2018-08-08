<?php

namespace Shopsys\FrameworkBundle\Component\Localization;

class DateTimeFormatPattern
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * @var string
     */
    private $locale;

    /**
     * @link http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     * @var int|null
     */
    private $dateType;

    /**
     * @link http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     * @var int|null
     */
    private $timeType;

    /**
     * @param int|null $dateType @link http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     * @param int|null $timeType @link http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     */
    public function __construct(string $pattern, string $locale, ?int $dateType = null, ?int $timeType = null)
    {
        $this->pattern = $pattern;
        $this->locale = $locale;
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
     * @return int|null @link http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     */
    public function getDateType(): ?int
    {
        return $this->dateType;
    }

    /**
     * @return int|null @link http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     */
    public function getTimeType(): ?int
    {
        return $this->timeType;
    }
}
