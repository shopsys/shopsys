<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Seo;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\MessageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

final class SeoRobotsSettingFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('messageNegativeImpact', MessageType::class, [
                'message_level' => MessageType::MESSAGE_LEVEL_WARNING,
                'data' => t('Incorrect settings may have a negative impact on the correct functionality of the e-shop.'),
            ])
            ->add('content', TextareaType::class, [
                'required' => false,
                'label' => 'File content',
            ])
            ->add('actionBar', ActionBarType::class, [
                'save_label' => t('Save changes'),
            ]);
    }
}
