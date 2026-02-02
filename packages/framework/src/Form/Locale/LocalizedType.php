<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Locale;

use Override;
use Shopsys\FrameworkBundle\Component\Utils\Utils;
use Shopsys\FrameworkBundle\Form\FormTypeLayout;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocalizedType extends AbstractType
{
    public function __construct(
        private readonly Localization $localization,
        private readonly FormTypeLayout $formTypeLayout,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        Utils::setArrayDefaultValue($options['entry_options'], 'required', $options['required']);
        Utils::setArrayDefaultValue($options['entry_options'], 'constraints', []);

        $defaultLocaleOptions = $options['entry_options'];
        $otherLocaleOptions = $options['entry_options'];

        $defaultLocaleOptions['constraints'] = array_merge(
            $defaultLocaleOptions['constraints'],
            $options['main_constraints'],
        );

        $defaultLocaleOptions['required'] = $options['required'];
        $otherLocaleOptions['required'] = $options['required'] && $otherLocaleOptions['required'];

        foreach ($this->localization->getAdminEnabledLocales() as $locale) {
            if ($locale === $this->localization->getCurrentLocaleForTranslatableEntities()) {
                $builder->add(
                    $locale,
                    $options['entry_type'],
                    array_replace_recursive(['attr' => ['data-locale' => $locale]], $defaultLocaleOptions),
                );
            } else {
                $builder->add(
                    $locale,
                    $options['entry_type'],
                    array_replace_recursive(['attr' => ['data-locale' => $locale]], $otherLocaleOptions),
                );
            }
        }
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'compound' => true,
            'entry_type' => TextType::class,
            'entry_options' => [],
            'main_constraints' => [],
            'layout' => null,
            'display_mode' => 'stacked',
        ]);

        $resolver->setAllowedValues('display_mode', ['stacked', 'columns']);
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['layout'] === null) {
            $options['layout'] = $this->formTypeLayout->resolveLayoutType($options['entry_type']);
        }

        $view->vars['layout'] = $options['layout'];
        $view->vars['display_mode'] = $options['display_mode'];
    }
}
