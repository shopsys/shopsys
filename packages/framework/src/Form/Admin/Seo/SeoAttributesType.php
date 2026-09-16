<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Seo;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Seo\SeoAttributesData;
use Shopsys\FrameworkBundle\Model\Seo\SeoMetaRobotsEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class SeoAttributesType extends AbstractType
{
    public function __construct(
        private readonly SeoMetaRobotsEnum $seoMetaRobotsEnum,
        private readonly Domain $domain,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => false,
                'label' => 'Page title',
                'attr' => [
                    'data-js-recommended-length' => 60,
                ],
            ])
            ->add('metaDescription', TextareaType::class, [
                'required' => false,
                'label' => 'Meta description',
                'attr' => [
                    'data-js-recommended-length' => 160,
                ],
            ])
            ->add('h1', TextType::class, [
                'required' => $options['h1_required'],
                'constraints' => $options['h1_required'] ? [new Constraints\NotBlank(message: 'Please enter heading (H1)')] : [],
                'label' => 'Heading (H1)',
            ])
            ->add('metaRobots', ChoiceType::class, [
                'required' => false,
                'label' => 'Meta robots',
                'choices' => $this->seoMetaRobotsEnum->getAllChoices(),
                'choice_translation_domain' => false,
                'placeholder' => 'Default (not set)',
                'help' => t('The selected value is always used as is. "Default (not set)" leaves the decision to the storefront, which applies its own rules for this page, e.g. noindex for filtered listings, cart or checkout.'),
            ])
            ->add('canonicalUrl', UrlType::class, [
                'required' => false,
                'label' => 'Canonical URL',
                'default_protocol' => null,
                'constraints' => [
                    new Constraints\Url(protocols: ['https'], message: 'Canonical URL must be an absolute https:// URL'),
                ],
                'attr' => [
                    'data-controller' => 'canonical-url-cross-domain-warning',
                    'data-canonical-url-cross-domain-warning-domain-url-value' => $this->domain->getDomainConfigById($options['domain_id'])->getUrl(),
                    'data-canonical-url-cross-domain-warning-message-value' => t('This is a cross-domain canonical URL – it points to a different domain than the one being edited.'),
                    'data-action' => 'canonical-url-cross-domain-warning#updateMessage',
                ],
            ]);
    }

    #[Override]
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['placeholder_source_path'] === null) {
            return;
        }

        $placeholderSourceInputId = $this->resolvePlaceholderSourceInputId($form, $options);

        foreach (['title', 'h1'] as $fieldName) {
            $view[$fieldName]->vars['attr']['data-js-placeholder-source-input-id'] = $placeholderSourceInputId;
        }
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('domain_id');
        $resolver->setDefaults([
            'data_class' => SeoAttributesData::class,
            'label' => false,
            'placeholder_source_path' => null,
            'h1_required' => false,
        ]);
        $resolver->setAllowedTypes('placeholder_source_path', ['string[]', 'null']);
        $resolver->setAllowedTypes('h1_required', 'bool');
        $resolver->setAllowedTypes('domain_id', 'int');
        $resolver->setInfo(
            'placeholder_source_path',
            'Names of the nested fields leading from the form root to the input holding the entity name, e.g. ["basicInformation", "name"] — title and H1 placeholders mirror it. The "{locale}" and "{domain_id}" tokens are replaced by the locale and id of the domain given by "domain_id".',
        );
        $resolver->setInfo('h1_required', 'Makes the "Heading (H1)" field mandatory.');
    }

    /**
     * The id is composed the same way Symfony composes ids of nested fields. The path is walked through the real form tree,
     * so a misspelled path fails immediately instead of silently breaking the placeholder.
     *
     * @param array<string, mixed> $options
     */
    private function resolvePlaceholderSourceInputId(FormInterface $form, array $options): string
    {
        $domainConfig = $this->domain->getDomainConfigById($options['domain_id']);
        $sourceForm = $form->getRoot();
        $idParts = [$sourceForm->getName()];

        foreach ($options['placeholder_source_path'] as $pathElement) {
            $childName = str_replace(
                ['{locale}', '{domain_id}'],
                [$domainConfig->getLocale(), (string)$domainConfig->getId()],
                $pathElement,
            );
            $sourceForm = $sourceForm->get($childName);
            $idParts[] = $childName;
        }

        return implode('_', array_filter($idParts, static fn (string $idPart): bool => $idPart !== ''));
    }
}
