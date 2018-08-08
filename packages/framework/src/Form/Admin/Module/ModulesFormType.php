<?php

namespace Shopsys\FrameworkBundle\Form\Admin\Module;

use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Model\Module\ModuleList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModulesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('modules', FormType::class)
            ->add('save', SubmitType::class);

        $moduleList = $options['module_list'];
        /* @var $moduleList \Shopsys\FrameworkBundle\Model\Module\ModuleList */
        foreach ($moduleList->getNamesIndexedByLabel() as $moduleLabel => $moduleName) {
            $builder->get('modules')
                ->add($moduleName, YesNoType::class, [
                    'label' => $moduleLabel,
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('module_list')
            ->setAllowedTypes('module_list', ModuleList::class)
            ->setDefaults([
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
