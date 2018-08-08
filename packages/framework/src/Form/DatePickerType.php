<?php

namespace Shopsys\FrameworkBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DatePickerType extends AbstractType
{
    const FORMAT_PHP = 'dd.MM.yyyy';
    const FORMAT_JS = 'dd.mm.yy';

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'widget' => 'single_text',
            'format' => self::FORMAT_PHP,
        ]);
    }

    public function getParent()
    {
        return DateType::class;
    }
}
