<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AdditionalServicePriceAndVatTableByDomainsType extends AbstractType
{
    public function __construct(
        private readonly Domain $domain,
    ) {
    }

    #[Override]
    public function getParent(): string
    {
        return PriceAndVatTableByDomainsType::class;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('pricesIndexedByDomainId', []);
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $useProductVatRateByDomainId = $builder->create('useProductVatRateByDomainId', FormType::class, [
            'compound' => true,
            'label' => false,
        ]);

        $vatsIndexedByDomainId = $builder->create('vatsIndexedByDomainId', FormType::class, [
            'compound' => true,
            'label' => false,
        ]);

        foreach ($this->domain->getAdminEnabledDomains() as $domainConfig) {
            $useProductVatRateByDomainId->add((string)$domainConfig->getId(), YesNoType::class, [
                'label' => 'Use VAT rate of the product',
                'help' => t('Yes = the service is an ancillary supply and takes over the VAT rate of the product it is attached to in the cart. No = the service is a separate supply with its own VAT rate.'),
            ]);

            // the field is rendered as required (asterisk) but the VAT rate is needed only when the product VAT rate
            // is not used, so the NotBlank of VatChoiceType is disabled in favor of
            // AdditionalServiceFormType::validateVats()
            $vatsIndexedByDomainId->add((string)$domainConfig->getId(), VatChoiceType::class, [
                'domain_id' => $domainConfig->getId(),
                'constraints' => [],
                'placeholder' => '---',
            ]);
        }

        $builder->add($useProductVatRateByDomainId);
        $builder->add($vatsIndexedByDomainId);
    }
}
