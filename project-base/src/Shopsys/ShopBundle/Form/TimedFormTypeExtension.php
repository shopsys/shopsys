<?php

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Component\Form\FormTimeProvider;
use Shopsys\FrameworkBundle\Component\Form\TimedSpamValidationListener;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TimedFormTypeExtension extends AbstractTypeExtension
{
    const MINIMUM_FORM_FILLING_SECONDS = 5;
    const OPTION_ENABLED = 'timed_spam_enabled';
    const OPTION_MINIMUM_SECONDS = 'timed_spam_minimum_seconds';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Form\FormTimeProvider
     */
    private $formTimeProvider;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Form\FormTimeProvider $formTimeProvider
     */
    public function __construct(FormTimeProvider $formTimeProvider)
    {
        $this->formTimeProvider = $formTimeProvider;
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
            $options
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
     * @return string
     */
    public function getExtendedType()
    {
        return FormType::class;
    }
}
