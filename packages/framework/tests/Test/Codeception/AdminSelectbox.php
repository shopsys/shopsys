<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test\Codeception;

/**
 * Representation of a graphical selectbox that is used in administration
 * Allows to manipulate selectboxes and read their values
 * (The original input is hidden by JS and replaced by a graphical element, therefore it cannot be manipulated directly)
 */
class AdminSelectbox
{
    /**
     * @var \Tests\FrameworkBundle\Test\Codeception\ActorInterface
     */
    protected $tester;

    /**
     * @var string
     */
    protected $cssSelector;

    /**
     * @param \Tests\FrameworkBundle\Test\Codeception\ActorInterface $tester
     * @param string $cssSelector
     */
    protected function __construct(ActorInterface $tester, string $cssSelector)
    {
        $this->tester = $tester;
        $this->cssSelector = $cssSelector;
    }

    /**
     * @param \Tests\FrameworkBundle\Test\Codeception\ActorInterface $tester
     * @param string $cssSelector
     * @return \Tests\FrameworkBundle\Test\Codeception\AdminSelectbox
     */
    public static function createByCss(ActorInterface $tester, string $cssSelector): self
    {
        return new static($tester, $cssSelector);
    }

    /**
     * @param string $value
     */
    public function select(string $value): void
    {
        $this->tester->selectOptionByCssAndValue($this->cssSelector, $value);
    }
}
