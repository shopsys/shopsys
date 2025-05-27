<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Form\Admin\Chat;

use Override;
use Shopsys\AiToolsBundle\Model\Chat\Agent\AgentFacade;
use Shopsys\AiToolsBundle\Model\Chat\Chat;
use Shopsys\FormTypesBundle\ActionBarType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChatFormType extends AbstractType
{
    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\Agent\AgentFacade $agentFacade
     */
    public function __construct(
        protected readonly AgentFacade $agentFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', TextareaType::class, [
                'label' => t('Question'),
                'required' => true,
                'attr' => [
                    'class' => 'chat-question',
                ],
            ])
        ;

        if ($options['chat'] === null) {
            $builder->add('agent', ChoiceType::class, [
                'label' => t('Agent'),
                'required' => true,
                'choices' => $this->agentFacade->getEnabledAgents(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'multiple' => false,
                'expanded' => false,
            ]);
        }

        $builder->add('actionBar', ActionBarType::class, [
            'back_route' => 'shopsys_aitools_admin_chat_list',
            'entity' => $options['chat'],
        ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['chat'])
            ->setAllowedTypes('chat', [Chat::class, 'null'])
            ->setDefaults([
                'attr' => ['novalidate' => 'novalidate'],
                'data_class' => null,
            ]);
    }
}
