<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PersonalData;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class PersonalDataFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param mixed[] $options
     */
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
