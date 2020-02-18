<?php

namespace Shopsys\FrameworkBundle\Twig;

use BadMethodCallException;
use CommerceGuys\Intl\Formatter\NumberFormatter;
use CommerceGuys\Intl\NumberFormat\NumberFormatRepositoryInterface;
use Shopsys\FrameworkBundle\Model\Administration\AdministrationFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Twig_Extension;

class NumberFormatterExtension extends Twig_Extension
{
    protected const MINIMUM_FRACTION_DIGITS = 0;
    protected const MAXIMUM_FRACTION_DIGITS = 10;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    protected $localization;

    /**
     * @var \CommerceGuys\Intl\NumberFormat\NumberFormatRepositoryInterface
     */
    protected $numberFormatRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administration\AdministrationFacade|null
     */
    protected $administrationFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \CommerceGuys\Intl\NumberFormat\NumberFormatRepositoryInterface $numberFormatRepository
     * @param \Shopsys\FrameworkBundle\Model\Administration\AdministrationFacade|null $administrationFacade
     */
    public function __construct(
        Localization $localization,
        NumberFormatRepositoryInterface $numberFormatRepository,
        ?AdministrationFacade $administrationFacade = null
    ) {
        $this->localization = $localization;
        $this->numberFormatRepository = $numberFormatRepository;
        $this->administrationFacade = $administrationFacade;
    }

    /**
     * @required
     * @internal This function will be replaced by constructor injection in next major
     * @param \Shopsys\FrameworkBundle\Model\Administration\AdministrationFacade $administrationFacade
     */
    public function setAdministrationFacade(AdministrationFacade $administrationFacade)
    {
        if ($this->administrationFacade !== null && $this->administrationFacade !== $administrationFacade) {
            throw new BadMethodCallException(sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__));
        }
        if ($this->administrationFacade === null) {
            @trigger_error(sprintf('The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.', __METHOD__), E_USER_DEPRECATED);
            $this->administrationFacade = $administrationFacade;
        }
    }

    /**
     * @return \Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'formatNumber',
                [$this, 'formatNumber']
            ),
            new \Twig_SimpleFilter(
                'formatDecimalNumber',
                [$this, 'formatDecimalNumber']
            ),
            new \Twig_SimpleFilter(
                'formatPercent',
                [$this, 'formatPercent']
            ),
            new \Twig_SimpleFilter(
                'isInteger',
                [$this, 'isInteger']
            ),
        ];
    }

    /**
     * @param mixed $number
     * @param string|null $locale
     * @return string
     */
    public function formatNumber($number, $locale = null)
    {
        $numberFormatter = new NumberFormatter($this->numberFormatRepository, [
            'locale' => $this->getLocale($locale),
            'style' => 'decimal',
            'minimum_fraction_digits' => static::MINIMUM_FRACTION_DIGITS,
            'maximum_fraction_digits' => static::MAXIMUM_FRACTION_DIGITS,
        ]);

        return $numberFormatter->format($number);
    }

    /**
     * @param mixed $number
     * @param int $minimumFractionDigits
     * @param string|null $locale
     * @return string
     */
    public function formatDecimalNumber($number, $minimumFractionDigits, $locale = null)
    {
        $numberFormatter = new NumberFormatter($this->numberFormatRepository, [
            'locale' => $this->getLocale($locale),
            'style' => 'decimal',
            'minimum_fraction_digits' => $minimumFractionDigits,
            'maximum_fraction_digits' => static::MAXIMUM_FRACTION_DIGITS,
        ]);

        return $numberFormatter->format($number);
    }

    /**
     * @param mixed $number
     * @param string|null $locale
     * @return string
     */
    public function formatPercent($number, $locale = null)
    {
        $numberFormatter = new NumberFormatter($this->numberFormatRepository, [
            'locale' => $this->getLocale($locale),
            'style' => 'percent',
            'minimum_fraction_digits' => static::MINIMUM_FRACTION_DIGITS,
            'maximum_fraction_digits' => static::MAXIMUM_FRACTION_DIGITS,
        ]);

        return $numberFormatter->format($number / 100);
    }

    /**
     * @param string|null $locale
     * @return string
     */
    protected function getLocale($locale = null)
    {
        if ($locale !== null) {
            return $locale;
        }

        if ($this->administrationFacade->isInAdmin()) {
            return $this->localization->getAdminLocale();
        }

        return $this->localization->getLocale();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'number_formatter_extension';
    }

    /**
     * @param mixed $number
     * @return bool
     */
    public function isInteger($number)
    {
        return is_numeric($number) && (int)$number == $number;
    }
}
