<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Domain;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Form\DisplayOnlyDomainIconType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class DomainFormType extends AbstractType
{
    public const string FIELD_ICON = 'icon';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('domainIcon', DisplayOnlyDomainIconType::class, [
                'label' => 'Existing icon',
                'data' => $options['domain']->getId(),
                'show_domain_name' => false,
            ])
            ->add(self::FIELD_ICON, ImageUploadType::class, [
                'required' => false,
                'file_constraints' => [
                    new Constraints\Image([
                        'mimeTypes' => ['image/png'],
                        'mimeTypesMessage' => 'Image can be only in PNG format',
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ]),
                ],
                'info_text' => t('The optimal size of the icon is 46x26 px. Only PNG format is allowed.'),
            ])
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_domain_list',
                'save_label' => t('Save changes'),
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['attr' => ['novalidate' => 'novalidate']])
            ->setRequired('domain')
            ->setAllowedTypes('domain', [DomainConfig::class]);
    }
}
