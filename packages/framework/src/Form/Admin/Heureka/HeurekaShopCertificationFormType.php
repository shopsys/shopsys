<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Heureka;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class HeurekaShopCertificationFormType extends AbstractType
{
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

        $builderSettingsGroup
            ->add('heurekaApiKey', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\Length(
                        min: 32,
                        max: 32,
                        exactMessage: 'Heureka API must be {{ limit }} characters',
                    ),
                ],
                'label' => 'Code of service Heureka - Verified by Customer',
                'help' => t('Enter 32-digit code which will be sent to server') . ' ' . $options['server_name'],
            ])
            ->add('heurekaWidgetCode', TextareaType::class, [
                'required' => false,
                'label' => 'Heureka Widget code',
            ]);

        $builder
            ->add($builderSettingsGroup)
            ->add('actionBar', ActionBarType::class, [
                'save_label' => t('Save changes'),
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('server_name')
            ->setAllowedTypes('server_name', ['string', 'null'])
            ->setDefaults([
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
