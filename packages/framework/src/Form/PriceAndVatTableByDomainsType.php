<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Constraints\NotNegativeMoneyAmount;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class PriceAndVatTableByDomainsType extends AbstractType
{
    public function __construct(private readonly Domain $domain, private readonly VatFacade $vatFacade)
    {
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['pricesIndexedByDomainId'] = $options['pricesIndexedByDomainId'];
        $view->vars['enabledDomainIds'] = $this->domain->getAdminEnabledDomainIds();
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('pricesIndexedByDomainId')
            ->setAllowedTypes('pricesIndexedByDomainId', ['array']);
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $vatsIndexedByDomainId = $builder->create('vatsIndexedByDomainId', FormType::class, [
            'compound' => true,
            'label' => false,
        ]);

        $entityPricesByDomainId = $builder->create('pricesIndexedByDomainId', FormType::class, [
            'compound' => true,
            'label' => false,
        ]);

        foreach ($this->domain->getAdminEnabledDomains() as $domainConfig) {
            $vatsIndexedByDomainId->add((string)$domainConfig->getId(), ChoiceType::class, [
                'required' => true,
                'choices' => $this->vatFacade->getAllForDomain($domainConfig->getId()),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter VAT rate'),
                ],
                'label' => 'VAT',
            ]);

            $entityPricesByDomainId->add((string)$domainConfig->getId(), MoneyType::class, [
                'scale' => 6,
                'required' => true,
                'invalid_message' => 'Please enter price in correct format (positive number with decimal separator)',
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter price'),
                    new NotNegativeMoneyAmount(message: 'Price must be greater or equal to zero'),
                ],
                'label' => 'Price',
            ]);
        }

        $builder->add($vatsIndexedByDomainId);
        $builder->add($entityPricesByDomainId);
    }
}
