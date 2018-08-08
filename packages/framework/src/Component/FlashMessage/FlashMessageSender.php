<?php

namespace Shopsys\FrameworkBundle\Component\FlashMessage;

use Twig_Environment;

class FlashMessageSender
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\FlashMessage\Bag
     */
    private $flashMessageBag;

    /**
     * @var \Twig_Environment
     */
    private $twigEnvironment;

    public function __construct(
        Bag $flashMessageBag,
        Twig_Environment $twigEnvironment
    ) {
        $this->flashMessageBag = $flashMessageBag;
        $this->twigEnvironment = $twigEnvironment;
    }

    public function addErrorFlashTwig(string $template, array $parameters = []): void
    {
        $message = $this->renderStringTwigTemplate($template, $parameters);
        $this->flashMessageBag->addError($message, false);
    }

    public function addInfoFlashTwig(string $template, array $parameters = []): void
    {
        $message = $this->renderStringTwigTemplate($template, $parameters);
        $this->flashMessageBag->addInfo($message, false);
    }

    public function addSuccessFlashTwig(string $template, array $parameters = []): void
    {
        $message = $this->renderStringTwigTemplate($template, $parameters);
        $this->flashMessageBag->addSuccess($message, false);
    }

    private function renderStringTwigTemplate(string $template, array $parameters): string
    {
        $twigTemplate = $this->twigEnvironment->createTemplate($template);

        return $twigTemplate->render($parameters);
    }

    /**
     * @param string|array $message
     */
    public function addErrorFlash($message): void
    {
        $this->flashMessageBag->addError($message, true);
    }

    /**
     * @param string|array $message
     */
    public function addInfoFlash($message): void
    {
        $this->flashMessageBag->addInfo($message, true);
    }

    /**
     * @param string|array $message
     */
    public function addSuccessFlash($message): void
    {
        $this->flashMessageBag->addSuccess($message, true);
    }
}
