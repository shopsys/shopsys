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
    /**
     * @param string $template
     * @param mixed[] $parameters
     */
    public function addSuccessFlashTwig(string $template, array $parameters = []): void
    {
        $this->addSuccessFlash($this->renderStringTwigTemplate($template, $parameters));
    }

    /**
     * @param string $template
     * @param mixed[] $parameters
     */
    public function addErrorFlashTwig(string $template, array $parameters = []): void
    {
        $this->addErrorFlash($this->renderStringTwigTemplate($template, $parameters));
    }

    /**
     * @param string $template
     * @param mixed[] $parameters
     */
    public function addInfoFlashTwig(string $template, array $parameters = []): void
    {
        $this->addInfoFlash($this->renderStringTwigTemplate($template, $parameters));
    }

    /**
     * @param string $message
     */
    public function addErrorFlash(string $message): void
    {
        $this->addFlashMessage(FlashMessage::KEY_ERROR, $message);
    }

    /**
     * @param string $message
     */
    public function addInfoFlash(string $message): void
    {
        $this->addFlashMessage(FlashMessage::KEY_INFO, $message);
    }

    /**
     * @param string $message
     */
    public function addSuccessFlash(string $message): void
    {
        $this->addFlashMessage(FlashMessage::KEY_SUCCESS, $message);
    }

    /**
     * @param string $type
     * @param string $message
     */
    protected function addFlashMessage(string $type, string $message): void
    {
        $this->getSession()->getFlashBag()->add($type, $message);
    }

    /**
     * @param string $template
     * @param mixed[] $parameters
     * @return string
     */
    protected function renderStringTwigTemplate(string $template, array $parameters = []): string
    {
        /** @var \Twig\Environment $twigEnvironment */
        $twigEnvironment = $this->container->get('twig');
        $twigTemplate = $twigEnvironment->createTemplate($template);

        return $twigTemplate->render($parameters);
    }

    /**
     * @return bool
     */
    public function isFlashMessageBagEmpty(): bool
    {
        $flashBag = $this->getSession()->getFlashBag();

        return !$flashBag->has(FlashMessage::KEY_ERROR)
            && !$flashBag->has(FlashMessage::KEY_INFO)
            && !$flashBag->has(FlashMessage::KEY_SUCCESS);
    }

    /**
     * @return mixed[]
     */
    public function getErrorMessages(): array
    {
        return $this->getMessages(FlashMessage::KEY_ERROR);
    }

    /**
     * @return mixed[]
     */
    public function getInfoMessages(): array
    {
        return $this->getMessages(FlashMessage::KEY_INFO);
    }

    /**
     * @return mixed[]
     */
    public function getSuccessMessages(): array
    {
        return $this->getMessages(FlashMessage::KEY_SUCCESS);
    }

    /**
     * @param string $key
     * @return mixed[]
     */
    protected function getMessages($key): array
    {
        $flashBag = $this->getSession()->getFlashBag();
        $messages = $flashBag->get($key);

        return array_unique($messages);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
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
