<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Form\Admin\Mcp;

use DateTimeImmutable;
use DateTimeZone;
use Override;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Form\DatePickerType;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpToken;
use Shopsys\McpBundle\Model\Administrator\McpToken\ManualTokenExpirationPresetEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class GenerateManualTokenFormType extends AbstractType
{
    public function __construct(
        private readonly ManualTokenExpirationPresetEnum $manualTokenExpirationPresetEnum,
        private readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'Label',
                'constraints' => [
                    new NotBlank(message: 'Please enter token label'),
                    new Length(
                        max: AdministratorMcpToken::LABEL_MAX_LENGTH,
                        maxMessage: 'Token label cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('expirationPreset', ChoiceType::class, [
                'label' => 'Expiration',
                'choices' => $this->manualTokenExpirationPresetEnum->getAllIndexedByTranslations(),
                'attr' => [
                    'class' => 'js-mcp-manual-token-expiration-preset',
                ],
            ])
            ->add('expiresAt', DatePickerType::class, [
                'label' => 'Expires on',
                'required' => false,
                'row_attr' => [
                    'data-js-mcp-manual-token-custom-expiration' => null,
                    'style' => 'display: none',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Generate token',
            ]);

        $builder->addEventListener(FormEvents::SUBMIT, $this->normalizeAndValidateExpirationOnSubmit(...));
    }

    protected function normalizeAndValidateExpirationOnSubmit(FormEvent $event): void
    {
        $data = $event->getData();

        if (!is_array($data)) {
            return;
        }

        $expirationPreset = $data['expirationPreset'] ?? null;

        if (!is_string($expirationPreset)) {
            return;
        }

        if ($expirationPreset !== ManualTokenExpirationPresetEnum::PRESET_CUSTOM) {
            $data['expiresAt'] = $this->manualTokenExpirationPresetEnum->getExpiresAtByPreset($expirationPreset);
            $event->setData($data);

            return;
        }

        $expiresAtForm = $event->getForm()->get('expiresAt');
        $expiresAt = $data['expiresAt'] ?? null;

        if (!$expiresAt instanceof DateTimeImmutable) {
            if (count($expiresAtForm->getErrors()) === 0) {
                $expiresAtForm->addError(new FormError(t('Please enter expiration date')));
            }

            return;
        }

        $displayTimeZone = $this->displayTimeZoneProvider->getDisplayTimeZoneForAdmin();
        $selectedDateInDisplayTimeZone = $expiresAt->setTimezone($displayTimeZone);
        $today = new DateTimeImmutable('today', $displayTimeZone);

        if ($selectedDateInDisplayTimeZone < $today) {
            $expiresAtForm->addError(new FormError(t('Expiration date must be today or in the future')));

            return;
        }

        $data['expiresAt'] = $this->createEndOfDayInDisplayTimeZone($selectedDateInDisplayTimeZone, $displayTimeZone);
        $event->setData($data);
    }

    protected function createEndOfDayInDisplayTimeZone(
        DateTimeImmutable $dateTime,
        DateTimeZone $displayTimeZone,
    ): DateTimeImmutable {
        return (new DateTimeImmutable($dateTime->format('Y-m-d') . ' 23:59:59', $displayTimeZone))
            ->setTimezone(new DateTimeZone('UTC'));
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $today = new DateTimeImmutable('today', $this->displayTimeZoneProvider->getDisplayTimeZoneForAdmin());

        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
            'data' => [
                'label' => AdministratorMcpToken::DEFAULT_MANUAL_TOKEN_LABEL,
                'expirationPreset' => ManualTokenExpirationPresetEnum::PRESET_31_DAYS,
                'expiresAt' => $today,
            ],
            'method' => Request::METHOD_POST,
        ]);
    }
}
