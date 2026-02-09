<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\LuigisBoxBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Form\MessageType;
use Shopsys\ProductFeed\LuigisBoxBundle\Model\Setting\LuigisBoxFeedSettingEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

final class LuigisBoxSettingFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(LuigisBoxFeedSettingEnum::LUIGIS_BOX_RANK, IntegerType::class, [
                'label' => t('Luigi\'s Box rank'),
                'required' => true,
                'constraints' => [
                    new Constraints\NotNull(
                        message: 'Please enter the Luigi\'s Box rank.',
                    ),
                    new Constraints\Range(min: 1, max: 15),
                ],
            ])
            ->add('luigisBoxRankInfo', MessageType::class, [
                'message_level' => MessageType::MESSAGE_LEVEL_INFO,
                'data' => t('The value is used for availability_rank setting in Luigi\'s Box feed. See <a href="https://docs.luigisbox.com/indexing/feeds.html">the docs</a> for more information.'),
            ]);
    }
}
