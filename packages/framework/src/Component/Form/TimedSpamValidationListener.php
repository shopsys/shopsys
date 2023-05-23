<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Form;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class TimedSpamValidationListener implements EventSubscriberInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Form\FormTimeProvider $formTimeProvider
     * @param array $options
     */
    public function __construct(
        protected readonly FormTimeProvider $formTimeProvider,
        protected readonly array $options,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        if ($form->isRoot() &&
            $form->getConfig()->getOption('compound') &&
            !$this->formTimeProvider->isFormTimeValid($form->getName(), $this->options)
        ) {
            $message = t(
                '{1} You must wait 1 second before submitting the form.
                |[2,Inf] You must wait %seconds% seconds before submitting the form.',
                [
                    '%seconds%' => $this->options[TimedFormTypeExtension::OPTION_MINIMUM_SECONDS],
                    '%count%' => (int)$this->options[TimedFormTypeExtension::OPTION_MINIMUM_SECONDS],
                ],
            );
            $form->addError(new FormError($message));
        }
        $this->formTimeProvider->removeFormTime($form->getName());
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SUBMIT => 'preSubmit',
        ];
    }
}
