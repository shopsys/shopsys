<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test\Codeception;

use PHPUnit\Framework\Assert;

abstract class AbstractCheckbox
{
    /**
     * @param \Tests\FrameworkBundle\Test\Codeception\ActorInterface $tester
     * @param string $cssSelector
     */
    protected function __construct(protected readonly ActorInterface $tester, protected readonly string $cssSelector)
    {
    }

    /**
     * Method will mark the particular image element with a generated class via JS so it can be targeted by Selenium easily.
     *
     * @return string
     */
    abstract protected function getImageElementClass(): string;

    public function check(): void
    {
        $this->isChecked() ? $this->assertVisible() : $this->toggle();
    }

    protected function assertVisible(): void
    {
        $imageElementClass = $this->getImageElementClass();

        $this->tester->canSeeElement(['css' => '.' . $imageElementClass]);
    }

    /**
     * @return bool
     */
    protected function isChecked(): bool
    {
        $script = sprintf('return $("%s").is(":checked")', $this->cssSelector);

        return (bool)$this->tester->executeJS($script);
    }

    public function uncheck(): void
    {
        $this->isChecked() ? $this->toggle() : $this->assertVisible();
    }

    public function toggle(): void
    {
        $imageElementClass = '.' . $this->getImageElementClass();

        $this->tester->clickByCss($imageElementClass);
    }

    public function assertChecked(): void
    {
        $this->assertVisible();

        $message = sprintf('Admin checkbox "%s" should be checked but it\'s unchecked.', $this->cssSelector);
        Assert::assertTrue($this->isChecked(), $message);
    }

    public function assertUnchecked(): void
    {
        $this->assertVisible();

        $message = sprintf('Admin checkbox "%s" should be unchecked but it\'s checked.', $this->cssSelector);
        Assert::assertFalse($this->isChecked(), $message);
    }
}
