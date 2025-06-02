<?php

declare(strict_types=1);

namespace Shopsys\FormTypesBundle;

use Override;
use Shopsys\FormTypesBundle\Domain\DomainIdsProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MultidomainType extends AbstractType
{
    public const string LAYOUT_BLOCK = 'block';
    public const string LAYOUT_INLINE = 'inline';

    /**
     * @param \Shopsys\FormTypesBundle\Domain\DomainIdsProviderInterface $domainIdsProvider
     */
    public function __construct(
        private readonly DomainIdsProviderInterface $domainIdsProvider,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $entryOptions = $options['entry_options'];
        $entryOptions['required'] = ($options['required'] ?? false) && ($entryOptions['required'] ?? false);
        $entryOptions['constraints'] = $entryOptions['constraints'] ?? [];

        $domainIds = $this->domainIdsProvider->getAdminEnabledDomainIds();

        foreach ($domainIds as $domainId) {
            if (array_key_exists($domainId, $options['options_by_domain_id'])) {
                $domainOptions = array_merge($entryOptions, $options['options_by_domain_id'][$domainId]);
            } else {
                $domainOptions = $entryOptions;
            }

            $domainOptions['attr']['data-domain-id'] = $domainId;
            $domainOptions['label'] = false;

            $builder->add((string)$domainId, $options['entry_type'], $domainOptions);
        }
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'compound' => true,
            'entry_type' => TextType::class,
            'entry_options' => [],
            'options_by_domain_id' => [],
            'layout' => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['layout'] === null) {
            $options['layout'] = $this->guessLayout($options);
        }

        $view->vars['layout'] = $options['layout'];
    }

    /**
     * @param array<string, mixed> $options
     * @return string
     */
    private function guessLayout(array $options): string
    {
        // @todo better guess, make extendable, unify into single provider for localized
        $inlineTypes = [
            TextType::class,
            TextareaType::class,
            MoneyType::class,
        ];

        if (in_array($options['entry_type'], $inlineTypes, true)) {
            return self::LAYOUT_INLINE;
        }

        return self::LAYOUT_BLOCK;
    }
}
