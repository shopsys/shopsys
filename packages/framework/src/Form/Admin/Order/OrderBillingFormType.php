<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Order;

use Override;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class OrderBillingFormType extends AbstractType
{
    public function __construct(
        private readonly CountryFacade $countryFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Order\Order $order */
        $order = $options['order'];
        $countries = $this->countryFacade->getAllOnDomain($order->getDomainId());

        $builder
            ->add('companyName', TextType::class, [
                'label' => 'Company name',
                'required' => false,
                'constraints' => [
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'Company name cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('companyNumber', TextType::class, [
                'label' => 'Company number',
                'required' => false,
                'constraints' => [
                    new Constraints\Length(
                        max: 50,
                        maxMessage: 'Identification number cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('companyTaxNumber', TextType::class, [
                'label' => 'Tax number',
                'required' => false,
                'constraints' => [
                    new Constraints\Length(
                        max: 50,
                        maxMessage: 'Tax number cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('street', TextType::class, [
                'label' => 'Street',
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter street'),
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'Street name cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'City',
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter city'),
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'City name cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('postcode', TextType::class, [
                'label' => 'Postcode',
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter zip code'),
                    new Constraints\Length(
                        max: 30,
                        maxMessage: 'Zip code cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('country', ChoiceType::class, [
                'label' => 'Country',
                'choices' => $countries,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please choose country'),
                ],
            ]);

        // Order::fillCommonFields() persists company data only when isCompanyCustomer=true.
        // Infer the flag from companyName when this partial billing form is submitted.
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
            $data = $event->getData();

            if ($data instanceof OrderData) {
                $data->isCompanyCustomer = $data->companyName !== null && $data->companyName !== '';
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('order')
            ->setAllowedTypes('order', Order::class)
            ->setDefaults([
                'inherit_data' => true,
            ]);
    }
}
