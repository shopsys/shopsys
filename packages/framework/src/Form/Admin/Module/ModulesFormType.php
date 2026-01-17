<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Module;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Model\Module\ModuleList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ModulesFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('modules', GroupType::class, [
                'label' => 'Modules',
            ])
            ->add('actionBar', ActionBarType::class, [
                'save_label' => t('Save changes'),
            ]);

        /** @var \Shopsys\FrameworkBundle\Model\Module\ModuleList $moduleList */
        $moduleList = $options['module_list'];

        foreach ($moduleList->getNamesIndexedByLabel() as $moduleLabel => $moduleName) {
            $builder->get('modules')
                ->add($moduleName, YesNoType::class, [
                    'label' => $moduleLabel,
                ]);
        }
    }

    #[Override]
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
