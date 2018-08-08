<?php

namespace Shopsys\FrameworkBundle\Form\Admin\PersonalData;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class PersonalDataFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('personalDataDisplaySiteContent', CKEditorType::class, [
                'required' => false,
            ])
            ->add('personalDataExportSiteContent', CKEditorType::class, [
                'required' => false,

                ])
            ->add('save', SubmitType::class);
    }
}
