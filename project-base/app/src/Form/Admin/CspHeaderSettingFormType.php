<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class CspHeaderSettingFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('cspHeader', TextareaType::class, [
            'required' => false,
            'label' => t('Content-Security-Policy header'),
        ]);
        $builder->add('save', SubmitType::class, [
            'label' => t('Save'),
        ]);
    }
}
