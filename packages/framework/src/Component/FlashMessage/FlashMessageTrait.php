<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FlashMessage;

use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\Session\Session as Session;

/**
 * @property \Psr\Container\ContainerInterface $container
 */
trait FlashMessageTrait
{
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

    protected function addFlashMessage(string $type, string $message): void
    {
        $this->getSession()->getFlashBag()->add($type, $message);
    }

    protected function renderStringTwigTemplate(string $template, array $parameters = []): string
    {
        /** @var \Twig\Environment $twigEnvironment */
        $twigEnvironment = $this->container->get('twig');
        $twigTemplate = $twigEnvironment->createTemplate($template);

        return $twigTemplate->render($parameters);
    }

    public function isFlashMessageBagEmpty(): bool
    {
        $flashBag = $this->getSession()->getFlashBag();

        return !$flashBag->has(FlashMessage::KEY_ERROR)
            && !$flashBag->has(FlashMessage::KEY_INFO)
            && !$flashBag->has(FlashMessage::KEY_SUCCESS)
            && !$flashBag->has(FlashMessage::KEY_WARNING);
    }

    /**
     * @return string[]
     */
    public function getErrorMessages()
    {
        return $this->getMessages(FlashMessage::KEY_ERROR);
    }

    /**
     * @return string[]
     */
    public function getInfoMessages()
    {
        return $this->getMessages(FlashMessage::KEY_INFO);
    }

    /**
     * @return string[]
     */
    public function getSuccessMessages()
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

    /**
     * @param string $key
     * @return string[]
     */
    protected function getMessages($key)
    {
        $flashBag = $this->getSession()->getFlashBag();
        $messages = $flashBag->get($key);

        return array_unique($messages);
    }

    protected function getSession(): Session
    {
        try {
            /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
            $session = $this->container->get('request_stack')->getSession();

            return $session;
        } catch (SessionNotFoundException|NotFoundExceptionInterface|ContainerExceptionInterface) {
            throw new LogicException(
                'You can not work with flash messages if sessions are disabled. Enable them in "config/packages/framework.yaml".',
            );
        }
    }
}
