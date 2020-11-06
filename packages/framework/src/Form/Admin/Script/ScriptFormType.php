<?php

namespace Shopsys\FrameworkBundle\Form\Admin\Script;

use Shopsys\FrameworkBundle\Model\Script\ScriptData;
use Shopsys\FrameworkBundle\Model\Script\ScriptFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class ScriptFormType extends AbstractType
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Script\ScriptFacade
     */
    protected $scriptFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Script\ScriptFacade $scriptFacade
     */
    public function __construct(ScriptFacade $scriptFacade)
    {
        $this->scriptFacade = $scriptFacade;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter script name']),
                ],
            ])
            ->add('code', TextareaType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter script code']),
                ],
                'attr' => [
                    'class' => 'height-150',
                ],
            ])
            ->add('placement', ChoiceType::class, [
                'required' => true,
                'choices' => $this->scriptFacade->getAvailablePlacementChoices(),
                'attr' => [
                    'class' => 'js-measure-script-placement-choice',
                ],
            ])
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ScriptData::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
