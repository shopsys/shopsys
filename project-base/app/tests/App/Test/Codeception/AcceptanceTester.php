<?php

declare(strict_types=1);

namespace Tests\App\Test\Codeception;

use Closure;
use Codeception\Actor;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tests\App\Test\Codeception\_generated\AcceptanceTesterActions;
use Tests\FrameworkBundle\Test\Codeception\ActorInterface;

/**
 * Inherited Methods
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void haveFriend($name, $actorClass = null)
 * @method \Codeception\Scenario getScenario()
 */
class AcceptanceTester extends Actor implements ActorInterface
{
    protected const DEFAULT_AJAX_TIMEOUT_SEC = 10;
    protected const WAIT_TIMEOUT_SEC = 10;

    use AcceptanceTesterActions;

    public function switchToLastOpenedWindow(): void
    {
        // workaround for a race condition when windows get enumerated before the new window is opened
        $this->wait(1);

        $closure = Closure::fromCallable(function (RemoteWebDriver $webdriver): void {
            $handles = $webdriver->getWindowHandles();
            $lastWindow = end($handles);
            $this->switchToWindow($lastWindow);
        });

        $this->executeInSelenium($closure);
        $this->waitForElement('body', self::WAIT_TIMEOUT_SEC);
    }

    /**
     * @param int $timeout
     */
    public function waitForAjax(int $timeout = self::DEFAULT_AJAX_TIMEOUT_SEC): void
    {
        $this->waitForJS('return $.active == 0;', $timeout);
    }

    public function pauseExecution(): void
    {
    }
}
