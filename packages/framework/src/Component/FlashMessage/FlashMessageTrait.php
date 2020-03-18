<?php

namespace Shopsys\FrameworkBundle\Component\FlashMessage;

/**
 * @property \Psr\Container\ContainerInterface $container
 */
trait FlashMessageTrait
{
    /**
     * @param string $template
     * @param array $parameters
     */
    public function addSuccessFlashTwig(string $template, array $parameters = []): void
    {
        $this->addSuccessFlash($this->renderStringTwigTemplate($template, $parameters));
    }

    /**
     * @param string $template
     * @param array $parameters
     */
    public function addErrorFlashTwig(string $template, array $parameters = []): void
    {
        $this->addErrorFlash($this->renderStringTwigTemplate($template, $parameters));
    }

    /**
     * @param string $template
     * @param array $parameters
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
        if (!$this->container->has('session')) {
            throw new \LogicException('You can not use the addFlash method if sessions are disabled. Enable them in "config/packages/framework.yaml".');
        }

        $this->container->get('session')->getFlashBag()->add($type, $message);
    }

    /**
     * @param string $template
     * @param array $parameters
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
    public function isFlashMessageBagEmpty()
    {
        /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface */
        $flashBag = $this->container->get('session')->getFlashBag();

        return !$flashBag->has(FlashMessage::KEY_ERROR)
            && !$flashBag->has(FlashMessage::KEY_INFO)
            && !$flashBag->has(FlashMessage::KEY_SUCCESS);
    }

    /**
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->getMessages(FlashMessage::KEY_ERROR);
    }

    /**
     * @return array
     */
    public function getInfoMessages()
    {
        return $this->getMessages(FlashMessage::KEY_INFO);
    }

    /**
     * @return array
     */
    public function getSuccessMessages()
    {
        return $this->getMessages(FlashMessage::KEY_SUCCESS);
    }

    /**
     * @param string $key
     * @return array
     */
    protected function getMessages($key)
    {
        /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface */
        $flashBag = $this->container->get('session')->getFlashBag();
        $messages = $flashBag->get($key);

        return array_unique($messages);
    }
}
