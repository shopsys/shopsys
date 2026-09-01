<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

/**
 * Select of VAT rates available on the given domain
 */
final class VatChoiceType extends AbstractType
{
    public function __construct(
        private readonly VatFacade $vatFacade,
    ) {
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('domain_id')
            ->setAllowedTypes('domain_id', 'int')
            ->setDefaults([
                'choices' => fn (Options $options): array => $this->vatFacade->getAllForDomain($options['domain_id']),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'choice_translation_domain' => false,
                'label' => 'VAT',
                'required' => true,
                'constraints' => fn (Options $options): array => $options['required']
                    ? [new Constraints\NotBlank(message: 'Please enter VAT rate')]
                    : [],
            ]);
    }

    #[Override]
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
