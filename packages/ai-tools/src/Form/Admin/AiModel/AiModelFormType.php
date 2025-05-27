<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Form\Admin\AiModel;

use Override;
use Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModel;
use Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelData;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AiModelFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builderAiModelData = $builder->create('agentData', GroupType::class, [
            'label' => t('Information about AI model'),
        ]);

        $builderAiModelData
            ->add('name', TextType::class, [
                'label' => t('Model name'),
                'required' => true,
                'disabled' => true,
            ])
            ->add('description', TextType::class, [
                'label' => t('Model description'),
                'required' => false,
            ])
            ->add('isActive', YesNoType::class, [
                'label' => t('Is model active'),
                'required' => true,
            ])
            ->add('isDeprecated', YesNoType::class, [
                'label' => t('Is model depracated'),
                'required' => true,
            ]);

        $builder->add('actionBar', ActionBarType::class, [
            'back_route' => 'shopsys_aitools_admin_aimodel_list',
            'entity' => $options['aiModel'],
        ]);

        $builder->add($builderAiModelData);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['aiModel'])
            ->setAllowedTypes('aiModel', [AiModel::class, 'null'])
            ->setDefaults([
                'data_class' => AiModelData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
