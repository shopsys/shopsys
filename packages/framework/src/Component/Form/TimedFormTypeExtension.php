<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Form;

use Override;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TimedFormTypeExtension extends AbstractTypeExtension
{
    public const int MINIMUM_FORM_FILLING_SECONDS = 5;
    public const string OPTION_ENABLED = 'timed_spam_enabled';
    public const string OPTION_MINIMUM_SECONDS = 'timed_spam_minimum_seconds';

    public function __construct(protected readonly FormTimeProvider $formTimeProvider)
    {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$options[self::OPTION_ENABLED]) {
            return;
        }

        $builder->addEventSubscriber(new TimedSpamValidationListener(
            $this->formTimeProvider,
            $options,
        ));
    }

    #[Override]
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options[self::OPTION_ENABLED] && !$view->parent && $options['compound']) {
            $this->formTimeProvider->generateFormTime($form->getName());
        }
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            self::OPTION_ENABLED => false,
            self::OPTION_MINIMUM_SECONDS => self::MINIMUM_FORM_FILLING_SECONDS,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getExtendedTypes(): iterable
    {
        yield FormType::class;
    }
}
