<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Form\Admin\Agent;

use Override;
use Shopsys\AiToolsBundle\Model\Chat\Agent\Agent;
use Shopsys\AiToolsBundle\Model\Chat\Agent\AgentData;
use Shopsys\AiToolsBundle\Model\Chat\Agent\FunctionCalling\DynamicFunctionRunner;
use Shopsys\AiToolsBundle\Model\Chat\Agent\FunctionCalling\FunctionRunnerSetup;
use Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModel;
use Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelFacade;
use Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStoreFacade;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgentFormType extends AbstractType
{
    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\Agent\FunctionCalling\DynamicFunctionRunner $dynamicFunctionRunner
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStoreFacade $vectorStoreFacade
     * @param \Shopsys\AiToolsBundle\Model\Chat\AiModel\AiModelFacade $aiModelFacade
     */
    public function __construct(
        protected readonly DynamicFunctionRunner $dynamicFunctionRunner,
        protected readonly VectorStoreFacade $vectorStoreFacade,
        protected readonly AiModelFacade $aiModelFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builderAgentData = $builder->create('agentData', GroupType::class, [
            'label' => t('Agent setup'),
        ]);

        $builderAgentData
            ->add('name', TextType::class, [
                'label' => t('Name'),
                'required' => true,
            ])
            ->add('internalKey', TextType::class, [
                'label' => t('Internal Key'),
                'required' => true,
                'disabled' => $options['agent'] !== null,
            ])
            ->add('enabled', YesNoType::class, [
                'label' => t('Enabled'),
                'required' => false,
            ])
            ->add('aiModel', EntityType::class, [
                'class' => AiModel::class,
                'choice_label' => 'name',
                'label' => t('Model'),
                'required' => true,
            ])
            ->add('setup', TextareaType::class, [
                'label' => t('Setup'),
                'required' => false,
            ])
            ->add('availableAiFunctions', ChoiceType::class, [
                'label' => t('Available Ai functions'),
                'required' => false,
                'choices' => array_map(fn (FunctionRunnerSetup $setup) => $setup->aiFunctionName, $this->dynamicFunctionRunner->getAvailableFunctionList()),
                'multiple' => true,
            ])
            ->add('vectorStores', ChoiceType::class, [
                'label' => t('Available vector stores'),
                'required' => false,
                'multiple' => true,
                'choices' => $this->vectorStoreFacade->findAll(),
                'choice_label' => 'name',
                'choice_value' => 'id',
            ])
        ;

        $builder->add($builderAgentData);
        $builder->add('actionBar', ActionBarType::class, [
            'back_route' => 'shopsys_aitools_admin_agent_list',
            'entity' => $options['agent'],
        ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['agent'])
            ->setAllowedTypes('agent', [Agent::class, 'null'])
            ->setDefaults([
                'data_class' => AgentData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
