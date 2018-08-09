<?php

namespace Shopsys\FrameworkBundle\Form\Admin\TransportAndPayment;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class FreeTransportAndPaymentPriceLimitsFormType extends AbstractType
{
    const DOMAINS_SUBFORM_NAME = 'priceLimits';
    const FIELD_ENABLED = 'enabled';
    const FIELD_PRICE_LIMIT = 'priceLimit';
    const VALIDATION_GROUP_PRICE_LIMIT_ENABLED = 'priceLimitEnabled';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($this->getPriceLimitsBuilder($builder))
            ->add('save', SubmitType::class);
    }

    /**
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    private function getPriceLimitsBuilder(FormBuilderInterface $builder)
    {
        $formBuilderForDomains = $builder->create(self::DOMAINS_SUBFORM_NAME, null, ['compound' => true]);

        foreach ($this->domain->getAll() as $domainConfig) {
            $formBuilderForDomain = $builder->create($domainConfig->getId(), null, [
                    'compound' => true,
                    'validation_groups' => function (FormInterface $form) {
                        $validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];
                        $formData = $form->getData();
                        if ($formData[FreeTransportAndPaymentPriceLimitsFormType::FIELD_ENABLED]) {
                            $validationGroups[] = FreeTransportAndPaymentPriceLimitsFormType::VALIDATION_GROUP_PRICE_LIMIT_ENABLED;
                        }

                        return $validationGroups;
                    },
                ])
                ->add(self::FIELD_ENABLED, CheckboxType::class, [
                    'required' => false,
                ])
                ->add(self::FIELD_PRICE_LIMIT, MoneyType::class, [
                    'required' => true,
                    'currency' => false,
                    'constraints' => [
                        new Constraints\GreaterThanOrEqual([
                            'value' => 0,
                            'message' => 'Price must be greater or equal to {{ compared_value }}',
                            'groups' => [self::VALIDATION_GROUP_PRICE_LIMIT_ENABLED],
                        ]),
                        new Constraints\NotBlank([
                            'message' => 'Please enter price',
                            'groups' => [self::VALIDATION_GROUP_PRICE_LIMIT_ENABLED],
                        ]),
                    ],
                ]);

            $formBuilderForDomains->add($formBuilderForDomain);
        }

        return $formBuilderForDomains;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
