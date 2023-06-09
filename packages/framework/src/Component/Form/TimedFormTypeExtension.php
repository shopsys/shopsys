<?php

namespace Shopsys\FrameworkBundle\Component\Form;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TimedFormTypeExtension extends AbstractTypeExtension
{
    public const MINIMUM_FORM_FILLING_SECONDS = 5;
    public const OPTION_ENABLED = 'timed_spam_enabled';
    public const OPTION_MINIMUM_SECONDS = 'timed_spam_minimum_seconds';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Form\FormTimeProvider $formTimeProvider
     */
    public function __construct(protected readonly FormTimeProvider $formTimeProvider)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options[self::OPTION_ENABLED]) {
            return;
        }

        $builder->addEventSubscriber(new TimedSpamValidationListener(
            $this->formTimeProvider,
            $options,
        ));
    }

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if ($options[self::OPTION_ENABLED] && !$view->parent && $options['compound']) {
            $this->formTimeProvider->generateFormTime($form->getName());
        }
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            self::OPTION_ENABLED => false,
            self::OPTION_MINIMUM_SECONDS => self::MINIMUM_FORM_FILLING_SECONDS,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield FormType::class;
    }
}
