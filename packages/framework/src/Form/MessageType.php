<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MessageType extends AbstractType
{
    public const string MESSAGE_LEVEL_WARNING = 'warning';
    public const string MESSAGE_LEVEL_INFO = 'info';

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined('message_level')
            ->setAllowedValues('message_level', [self::MESSAGE_LEVEL_WARNING, self::MESSAGE_LEVEL_INFO])
            ->setDefaults([
                'mapped' => false,
                'required' => false,
                'message_level' => self::MESSAGE_LEVEL_WARNING,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['message_level'] = $options['message_level'];
    }
}
