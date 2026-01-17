<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\CspHeaderSetting;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\MessageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

final class CspHeaderSettingFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('warningMessage', MessageType::class, [
            'message_level' => MessageType::MESSAGE_LEVEL_WARNING,
            'data' => t('It is highly recommended to test this setting on devel or CI server before it is applied in production!'),
        ]);

        $builder->add('cspHeader', TextareaType::class, [
            'required' => false,
            'label' => 'Content-Security-Policy header',
        ]);
        $builder->add('actionBar', ActionBarType::class, [
            'save_label' => t('Save changes'),
        ]);
    }
}
