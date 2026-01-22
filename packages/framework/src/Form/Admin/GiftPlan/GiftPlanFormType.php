<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\GiftPlan;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\DatePickerType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ProductsType;
use Shopsys\FrameworkBundle\Form\ProductType;
use Shopsys\FrameworkBundle\Form\Transformers\EndOfDayTransformer;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlan;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class GiftPlanFormType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Form\Transformers\EndOfDayTransformer $endOfDayTransformer
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        private readonly EndOfDayTransformer $endOfDayTransformer,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => 'Settings',
        ]);

        if ($options['giftPlan'] !== null) {
            $builderSettingsGroup
                ->add('id', DisplayOnlyType::class, [
                    'data' => $options['giftPlan']->getId(),
                    'label' => 'ID',
                ])
                ->add('domainId', DisplayOnlyType::class, [
                    'data' => $this->domain->getDomainConfigById($options['giftPlan']->getDomainId())->getName(),
                    'label' => 'Domain',
                ]);
        } else {
            $builderSettingsGroup
                ->add('domainId', DomainType::class, [
                    'required' => true,
                    'data' => $this->adminDomainTabsFacade->getSelectedDomainId(),
                    'label' => 'Domain',
                ]);
        }

        $builderSettingsGroup
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter name of gift plan'),
                    new Constraints\Length(max: 255, maxMessage: 'Name cannot be longer than {{ limit }} characters'),
                ],
                'label' => 'Name',
                'help' => t('Name serves only for internal use within the administration.'),
            ])
            ->add('giftProduct', ProductType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please choose gift product'),
                ],
                'label' => 'Gift product',
            ])
            ->add('validFrom', DatePickerType::class, [
                'required' => false,
                'label' => 'Valid from',
            ])
            ->add('validTo', DatePickerType::class, [
                'required' => false,
                'label' => 'Valid to',
            ]);
        $builderSettingsGroup->get('validTo')->addModelTransformer($this->endOfDayTransformer);

        $builder
            ->add($builderSettingsGroup)
            ->add('mainProducts', ProductsType::class, [
                'required' => false,
                'label' => 'Main products',
            ])
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_giftplan_list',
                'entity' => $options['giftPlan'],
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['giftPlan'])
            ->setAllowedTypes('giftPlan', [GiftPlan::class, 'null'])
            ->setDefaults([
                'data_class' => GiftPlanData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
