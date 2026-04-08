<?php

declare(strict_types=1);

namespace Shopsys\FormTypesBundle;

use Override;
use Shopsys\FormTypesBundle\Domain\DomainIdsProviderInterface;
use Shopsys\FrameworkBundle\Form\FormTypeLayout;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MultidomainType extends AbstractType
{
    public function __construct(
        private readonly DomainIdsProviderInterface $domainIdsProvider,
        private readonly FormTypeLayout $formTypeLayout,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $entryOptions = $options['entry_options'];
        $entryOptions['required'] = ($options['required'] ?? false) || ($entryOptions['required'] ?? false);
        $entryOptions['constraints'] = $entryOptions['constraints'] ?? [];

        $domainIds = $this->domainIdsProvider->getAdminEnabledDomainIds();

        foreach ($domainIds as $domainId) {
            if (array_key_exists($domainId, $options['options_by_domain_id'])) {
                $domainOptions = array_merge_recursive($entryOptions, $options['options_by_domain_id'][$domainId]);
            } else {
                $domainOptions = $entryOptions;
            }

            $domainOptions['label'] = false;

            $builder->add((string)$domainId, $options['entry_type'], $domainOptions);
        }
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'compound' => true,
            'entry_type' => TextType::class,
            'entry_options' => [],
            'options_by_domain_id' => [],
            'layout' => null,
            'display_mode' => 'stacked',
        ]);

        $resolver->setAllowedValues('display_mode', ['stacked', 'columns']);
    }

    #[Override]
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        foreach ($view->children as $domainId => $childView) {
            $childView->vars['attr']['data-domain-id'] = $domainId;
        }
    }

    /**
     * {@inheritdoc}
     */
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
