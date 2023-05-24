<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Superadmin;

use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\Admin\Mail\MailWhitelistCollectionType;
use Shopsys\FrameworkBundle\Form\Constraints\WhitelistPattern;
use Shopsys\FrameworkBundle\Form\Transformers\MailWhitelistTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

final class MailWhitelistFormType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Form\Transformers\MailWhitelistTransformer $mailWhitelistTransformer
     */
    public function __construct(
        private readonly MailWhitelistTransformer $mailWhitelistTransformer,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mailWhitelistEnabled', YesNoType::class, [
                'label' => t('Enable whitelist'),
                'required' => true,
            ])
            ->add('mailWhitelist', MailWhitelistCollectionType::class, [
                'label' => t('E-mail addresses whitelist'),
                'entry_type' => TextType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'required' => false,
                'allow_add' => true,
                'error_bubbling' => false,
                'allow_delete' => true,
                'constraints' => [
                    new Constraints\All([
                        new WhitelistPattern(),
                    ]),
                ],
            ])
            ->add('save', SubmitType::class);

        $builder->addModelTransformer($this->mailWhitelistTransformer);
    }
}
