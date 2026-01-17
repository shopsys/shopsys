<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Vat;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class VatSettingsFormType extends AbstractType
{
    public function __construct(
        private readonly VatFacade $vatFacade,
        private readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('defaultVat', ChoiceType::class, [
                'required' => true,
                'choices' => $this->vatFacade->getAllForDomain($this->adminDomainTabsFacade->getSelectedDomainId()),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter default VAT'),
                ],
                'label' => 'Default VAT rate',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save default VAT rate',
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
