<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class EmptyMessageChoiceTypeExtension extends AbstractTypeExtension
{
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['empty_message'] = $options['empty_message'];
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('empty_message')
            ->setAllowedTypes('empty_message', 'string')
            ->setDefaults([
                'empty_message' => t('Nothing to choose from.'),
            ]);
    }

    #[Override]
    public static function getExtendedTypes(): iterable
    {
        yield ChoiceType::class;
    }
}
