<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FlashMessage;

use LogicException;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Twig\Environment;

class FlashMessageService
{
    public function __construct(
        protected readonly RequestStack $requestStack,
        protected readonly Environment $twigEnvironment,
    ) {
    }

    public function addSuccessFlashTwig(string $template, array $parameters = []): void
    {
        $this->addSuccessFlash($this->renderStringTwigTemplate($template, $parameters));
    }

    public function addErrorFlashTwig(string $template, array $parameters = []): void
    {
        $this->addErrorFlash($this->renderStringTwigTemplate($template, $parameters));
    }

    public function addInfoFlashTwig(string $template, array $parameters = []): void
    {
        $this->addInfoFlash($this->renderStringTwigTemplate($template, $parameters));
    }

    public function addErrorFlash(string $message): void
    {
        $this->addFlashMessage(FlashMessage::KEY_ERROR, $message);
    }

    public function addInfoFlash(string $message): void
    {
        $this->addFlashMessage(FlashMessage::KEY_INFO, $message);
    }

    public function addSuccessFlash(string $message): void
    {
        $this->addFlashMessage(FlashMessage::KEY_SUCCESS, $message);
    }

    public function addWarningFlash(string $message): void
    {
        $this->addFlashMessage(FlashMessage::KEY_WARNING, $message);
    }

    public function isFlashMessageBagEmpty(): bool
    {
        $flashBag = $this->getSession()->getFlashBag();

        return !$flashBag->has(FlashMessage::KEY_ERROR)
            && !$flashBag->has(FlashMessage::KEY_INFO)
            && !$flashBag->has(FlashMessage::KEY_SUCCESS)
            && !$flashBag->has(FlashMessage::KEY_WARNING);
    }

    public function hasErrorMessages(): bool
    {
        return $this->getSession()->getFlashBag()->has(FlashMessage::KEY_ERROR);
    }

    /**
     * @return string[]
     */
    public function getErrorMessages(): array
    {
        return $this->getMessages(FlashMessage::KEY_ERROR);
    }

    /**
     * @return string[]
     */
    public function getInfoMessages(): array
    {
        return $this->getMessages(FlashMessage::KEY_INFO);
    }

    /**
     * @return string[]
     */
    public function getSuccessMessages(): array
    {
        return $this->getMessages(FlashMessage::KEY_SUCCESS);
    }

    /**
     * @return string[]
     */
    public function getWarningMessages(): array
    {
        return $this->getMessages(FlashMessage::KEY_WARNING);
    }

    protected function addFlashMessage(string $type, string $message): void
    {
        $this->getSession()->getFlashBag()->add($type, $message);
    }

    protected function renderStringTwigTemplate(string $template, array $parameters = []): string
    {
        $twigTemplate = $this->twigEnvironment->createTemplate($template);

        return $twigTemplate->render($parameters);
    }

    /**
     * @return string[]
     */
    protected function getMessages(string $key): array
    {
        $flashBag = $this->getSession()->getFlashBag();
        $messages = $flashBag->get($key);

        return array_unique($messages);
    }

    protected function getSession(): Session
    {
        try {
            /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
            $session = $this->requestStack->getSession();

            return $session;
        } catch (SessionNotFoundException) {
            throw new LogicException(
                'You can not work with flash messages if sessions are disabled. Enable them in "config/packages/framework.yaml".',
            );
        }
    }
}
