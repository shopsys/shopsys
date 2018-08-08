<?php

namespace Shopsys\FrameworkBundle\Component\Error;

use Symfony\Bundle\TwigBundle\Controller\ExceptionController as BaseController;

class ExceptionController extends BaseController
{
    /**
     * @var bool
     */
    private $showErrorPagePrototype = false;
    
    public function setDebug(bool $bool): void
    {
        $this->debug = $bool;
    }

    public function getDebug(): bool
    {
        return $this->debug;
    }

    public function isShownErrorPagePrototype(): bool
    {
        return $this->showErrorPagePrototype;
    }

    public function setShowErrorPagePrototype(): void
    {
        $this->showErrorPagePrototype = true;
    }
}
