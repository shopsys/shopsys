<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Seo;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\MessageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class HreflangSettingFormType extends AbstractType
{
    public const string FIELD_HREFLANG_COLLECTION = 'hreflang_collection';

    public function __construct(
        protected readonly Domain $domain,
        protected readonly DomainConfigCollectionToDomainIdsTransformer $domainConfigCollectionToDomainIdsTransformer,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message_info', MessageType::class, [
                'message_level' => MessageType::MESSAGE_LEVEL_INFO,
                'data' => t('Hreflang tags help search engines understand the language and regional targeting of your content. By selecting multiple domains, you ensure that hreflang tags will be properly rendered in sitemap and on the page. Search engines then properly index and display the correct versions of your content to users across different regions.'),
            ])
            ->add('message_external_info', MessageType::class, [
                'message_level' => MessageType::MESSAGE_LEVEL_INFO,
                'data' => t('For more information about hreflang tags, see the <a href="https://developers.google.com/search/docs/specialty/international/localized-versions">Google documentation</a>'),
            ])
            ->add(self::FIELD_HREFLANG_COLLECTION, CollectionType::class, [
                'block_prefix' => 'hreflang_setting_collection',
                'label' => 'Alternate language domains',
                'entry_type' => ChoiceType::class,
                'entry_options' => [
                    'required' => true,
                    'choices' => $this->domain->getAll(),
                    'choice_label' => fn (DomainConfig $domain) => $domain->getName() . ' (' . $domain->getLocale() . ')',
                    'choice_value' => 'id',
                    'multiple' => true,
                    'expanded' => true,
                    'constraints' => [
                        new Callback(['callback' => [$this, 'validateLanguageUniquenessInCollectionItems']]),
                        new Count([
                            'min' => 2,
                            'minMessage' => 'At least two domains must be selected',
                        ]),
                    ],
                ],
                'required' => false,
                'allow_add' => true,
                'error_bubbling' => false,
                'allow_delete' => true,
                'constraints' => [
                    new Callback(['callback' => [$this, 'validateDomainUniqueness']]),
                ],
            ])
            ->add('actionBar', ActionBarType::class, [
                'save_label' => t('Save changes'),
            ]);

        $builder->get(self::FIELD_HREFLANG_COLLECTION)->addModelTransformer($this->domainConfigCollectionToDomainIdsTransformer);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[] $domainConfigs
     */
    public function validateLanguageUniquenessInCollectionItems(
        array $domainConfigs,
        ExecutionContextInterface $context,
    ): void {
        $selectedLocales = [];

        foreach ($domainConfigs as $domainConfig) {
            if (in_array($domainConfig->getLocale(), $selectedLocales, true)) {
                $context->buildViolation(t('Two selected domains cannot have the same language'))
                    ->addViolation();

                return;
            }

            $selectedLocales[] = $domainConfig->getLocale();
        }
    }

    /**
     * @param int[][] $domainIdCollection
     */
    public function validateDomainUniqueness(array $domainIdCollection, ExecutionContextInterface $context): void
    {
        $alreadyUsedDomains = [];

        foreach ($domainIdCollection as $domainIds) {
            foreach ($domainIds as $domainId) {
                $domainConfig = $this->domain->getDomainConfigById($domainId);

                if (in_array($domainConfig->getId(), $alreadyUsedDomains, true)) {
                    $context->buildViolation(t('One domain cannot be used in two groups'))
                        ->addViolation();

                    return;
                }

                $alreadyUsedDomains[] = $domainConfig->getId();
            }
        }
    }
}
