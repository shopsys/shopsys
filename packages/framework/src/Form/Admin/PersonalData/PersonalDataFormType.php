<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PersonalData;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

final class PersonalDataFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('personalDataDisplaySiteContent', CKEditorType::class, [
                'required' => false,
            ])
            ->add('personalDataExportSiteContent', CKEditorType::class, [
                'required' => false,
            ])
            ->add('actionBar', ActionBarType::class, [
                'save_label' => t('Save changes'),
            ]);
    }
}
