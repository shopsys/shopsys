<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Superadmin;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\Admin\Mail\MailWhitelistCollectionType;
use Shopsys\FrameworkBundle\Form\Constraints\WhitelistPattern;
use Shopsys\FrameworkBundle\Form\MessageType;
use Shopsys\FrameworkBundle\Form\Transformers\MailWhitelistTransformer;
use Shopsys\FrameworkBundle\Model\Mail\MailerSettingProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class MailWhitelistFormType extends AbstractType
{
    public function __construct(
        private readonly MailWhitelistTransformer $mailWhitelistTransformer,
        private readonly MailerSettingProvider $mailerSettingProvider,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($this->mailerSettingProvider->isWhitelistForced()) {
            $builder->add('messageIsForced', MessageType::class, [
                'message_level' => MessageType::MESSAGE_LEVEL_WARNING,
                'data' => t('E-mail whitelist is currently forced by MAILER_FORCE_WHITELIST environment variable. Whitelist is always active.'),
            ]);
        }

        $builder
            ->add('mailWhitelistEnabled', YesNoType::class, [
                'label' => 'Enable whitelist',
            ])
            ->add('messageMailWhitelist', MessageType::class, [
                'message_level' => MessageType::MESSAGE_LEVEL_INFO,
                'data' => t('E-mails are sent to addresses which comply with at least one regular expression. You can use <a href="https://regex101.com">https://regex101.com</a> for validating regular expressions.'),
            ])
            ->add('mailWhitelist', MailWhitelistCollectionType::class, [
                'label' => 'E-mail addresses whitelist',
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
            ->add('actionBar', ActionBarType::class, [
                'save_label' => t('Save changes'),
            ]);

        $builder->addModelTransformer($this->mailWhitelistTransformer);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            '',
        ]);
    }
}
