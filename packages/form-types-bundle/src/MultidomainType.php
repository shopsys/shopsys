<?php

declare(strict_types=1);

namespace Shopsys\FormTypesBundle;

use Override;
use Shopsys\FormTypesBundle\Domain\DomainIdsProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MultidomainType extends AbstractType
{
    /**
     * @param \Shopsys\FormTypesBundle\Domain\DomainIdsProviderInterface $domainIdsProvider
     */
    public function __construct(
        private readonly DomainIdsProviderInterface $domainIdsProvider,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
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
        ]);
    }
}
